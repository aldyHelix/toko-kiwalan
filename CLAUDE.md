# CLAUDE.md

File ini memberikan panduan kepada Claude Code (claude.ai/code) saat bekerja dengan kode di repositori ini.

## Tentang proyek ini

**Toko Kiwalan** — boilerplate e-commerce yang reusable, dibangun sebagai **modular monolith** di atas Laravel 12. Satu toko, banyak cabang, dengan 3D/AR product viewer sebagai fitur pembeda. Punya dua _entry surface_ yang coexist di atas domain yang sama: **panel admin Filament** dan **storefront Inertia + Vue 3**.

Proyek dibangun bertahap (Fase 0–8). Dokumen perencanaan di `docs/planning/` (ditulis dalam Bahasa Indonesia) adalah _source of truth_ untuk scope, peta modul, dan acceptance criteria per fase — baca `01-architecture.md` dan `04-task-list.md` sebelum mulai mengerjakan fitur, untuk tahu fase mana sebuah perubahan termasuk.

> Kondisi saat ini: **Fase 0 (scaffold)**. Baru modul `Shared` yang ada (sebagai template referensi). Modul bounded-context lain (Catalog, Ordering, Payment, dst.) dan sebagian besar paket `spatie/*` yang tercantum di `03-tech-stack.md` masih direncanakan, belum terinstall — tambahkan per-fase saat mengimplementasikannya.

## Perintah

PHP / backend (Composer script membungkus perintah kanonik — utamakan pakai ini):

```bash
composer dev            # server + queue + log pail + vite, jalan bersamaan (loop dev utama)
composer test           # config:clear lalu artisan test (Pest)
composer test:coverage  # test dengan coverage
composer lint           # Pint (auto-fix formatting)
composer lint:test      # Pint mode check (gate CI — tanpa menulis)
composer stan           # PHPStan/Larastan, level 6
php artisan test --filter=SharedClockTest          # jalankan satu test class
vendor/bin/pest tests/Unit/SharedClockTest.php     # jalankan satu test file
```

Frontend:

```bash
npm run dev          # Vite dev server (biasanya dijalankan via `composer dev`)
npm run build        # build produksi
npm run type-check   # vue-tsc --noEmit (gate CI)
npm run lint         # ESLint --fix
npm run lint:check   # ESLint mode check (gate CI)
npm run format       # Prettier write
npm run format:check # Prettier check (gate CI)
```

Setup pertama kali: `composer setup` (install, key, migrate, npm install, build).

CI (`.github/workflows/ci.yml`) menjalankan, dan harus tetap hijau: **Pint check → Larastan L6 → Pest (+coverage) → vue-tsc → ESLint → Prettier → build Vite**. CI berjalan di atas **PostgreSQL 17**; dev lokal default ke file SQLite di `database/database.sqlite`. Target coverage **≥80%**, ditegakkan mulai Fase 1.

## Arsitektur

### Modular monolith dengan bounded context

Kode bisnis berada di bawah `app/Modules/<Context>/`, bukan di folder default Laravel. Tiap modul punya empat layer dengan aturan dependensi mengarah ke dalam yang ketat:

```
Presentation ──▶ Application ──▶ Domain ◀── Infrastructure
```

```
app/Modules/<Context>/
├── Domain/            # Models (Eloquent merangkap entity), ValueObjects, Events, Contracts (port)
├── Application/       # Actions (1 class = 1 use case), DTO (immutable), Queries
├── Infrastructure/    # Eloquent*Repository (implements Domain\Contracts), Gateways, <Context>ServiceProvider
├── Presentation/      # Filament resources (admin) + Http controllers/requests (storefront)
└── Database/Migrations/   # milik modul, di-load otomatis oleh provider modul tsb.
```

Aturan yang **non-negotiable**:
- `Domain` tidak mengimpor apa pun ke luar (tidak ada Application/Infrastructure/Presentation).
- Presentation tidak pernah menjalankan query Eloquent business-logic secara langsung — ia memanggil `Action` atau `Query`.
- Akses lintas-modul **hanya** lewat `Domain/Contracts` modul lain atau domain event — tidak pernah lewat model/tabelnya langsung. Graf dependensi antar modul bersifat acyclic (lihat `01-architecture.md` §3).
- Pengecualian pragmatis: CRUD Filament sederhana boleh memakai model Eloquent langsung. SOLID diterapkan di tempat yang berbayar (payment, order state, resolusi stok), bukan secara dogmatis.

### Menambah modul

1. Buat skeleton layer di bawah `app/Modules/<Context>` (lihat `app/Modules/README.md`).
2. Tambahkan `Infrastructure/<Context>ServiceProvider` yang meng-extend `App\Modules\Shared\Infrastructure\ModuleServiceProvider`; implementasikan `modulePath()`, override `registerBindings()` (bind contract → impl Eloquent) dan `bootModule()` (route, policy) sesuai kebutuhan. `SharedServiceProvider` adalah template copy-paste — `ModuleServiceProvider::boot()` otomatis me-load `Database/Migrations`.
3. Daftarkan provider tersebut di `bootstrap/providers.php`.

PSR-4 me-resolve `App\Modules\<Context>\...` ke `app/Modules/<Context>/...` lewat mapping root `App\\ → app/` — tanpa konfigurasi Composer tambahan.

### Dua guard auth, dua entry surface

Admin dan storefront memakai **guard dan credential store terpisah** (lihat `config/auth.php`):

| | Admin | Storefront |
|---|---|---|
| Stack | Filament 5 (Livewire) | Inertia 3 + Vue 3 + TS |
| Route / path | panel `/admin` | `/` (`routes/web.php`) |
| Guard / model | `admin` / `App\Models\Admin` | `web` / `App\Models\User` |
| Akses data | Resource → Action/Repository | Controller → Action/Query |

Panel Filament dikonfigurasi di `app/Providers/Filament/AdminPanelProvider.php` (discover resources/pages/widgets di bawah `app/Filament/`). Kedua surface memanggil layer Application yang sama — business rule tidak pernah diduplikasi antar keduanya.

Inertia di-wire lewat `app/Http/Middleware/HandleInertiaRequests.php` (root view `resources/views/app.blade.php`), entry Vue `resources/js/app.ts` (halaman di `resources/js/Pages/`), dan Ziggy untuk route Laravel di JS. Tailwind v4 via `@tailwindcss/vite`; `@` di-alias ke `resources/js`.

## Konvensi

- **`declare(strict_types=1);`** di setiap file PHP — ditegakkan oleh Pint (`pint.json`). PSR-12, import diurut alfabetis.
- **Actions**: kata kerja + objek, satu method publik (`handle`/`__invoke`) — mis. `CreateProduct`, `PlaceOrder`, `HandleMidtransWebhook`.
- **Repositories**: interface di `Domain/Contracts`, impl `Eloquent*Repository` di `Infrastructure/Repositories`, di-bind di provider modul. Pisahkan contract baca/tulis (mis. `StockReader` / `StockWriter`) bila ISP membantu.
- **Immutability**: DTO (via `spatie/laravel-data`, `readonly`) dan Value Object (`Money`, `Sku`, `Slug`) bersifat immutable — kembalikan instance baru, jangan mutasi. Jangan mengoper array mentah antar layer.
- **Money**: selalu integer minor unit dibungkus value object `Money` dengan currency eksplisit (default IDR). Jangan pakai float untuk uang, jangan menambah kolom uang yang tidak didukung `Money`.
- **i18n**: teks produk/kategori yang user-facing bersifat translatable (`spatie/laravel-translatable`), locale default `id`.
- **Validasi & error**: validasi di boundary (Form Request untuk storefront, DTO factory untuk lainnya). Lempar exception domain spesifik (mis. `InsufficientStockException`) — pesan ramah di UI, log berkonteks di server, jangan ditelan diam-diam.
- **Webhooks** (Fase 6): route webhook payment bersifat no-CSRF dan **wajib** verifikasi signature serta idempoten (guard pada `gateway_ref` + status).
- **Tests**: Pest. TDD wajib untuk business rule (Action, domain service, state machine, resolver); feature test ringan boleh untuk CRUD Filament sederhana. Untuk respons Inertia, assert dengan `AssertableInertia` (nama komponen + props), bukan JSON mentah.

## Git

Conventional commits (`feat|fix|refactor|docs|test|chore|perf|ci: <desc>`). Buat branch sebelum commit di `main`; commit/push hanya saat diminta. Commit per task yang selesai — tidak ada task "selesai" tanpa test untuk business rule-nya dan quality gate yang hijau.
