# 03 — Tech Stack & Conventions

> Versi spesifik diverifikasi ke yang terbaru-stabil saat scaffold (Fase 0). Tabel di bawah adalah target.

## 1. Runtime & Framework

| Komponen | Versi target | Catatan |
|---|---|---|
| PHP | 8.3+ | typed properties, enums, readonly |
| Laravel | 12.x | framework utama |
| Node | 20 LTS+ | build frontend |
| Database | PostgreSQL 16/17 | `jsonb` columns untuk translatable & payload (indexable, native JSON ops) |
| Redis | 7+ | cache, queue, session (prod) |

## 2. Composer Packages

| Package | Untuk | Modul |
|---|---|---|
| `inertiajs/inertia-laravel` | adapter Inertia | Presentation |
| `tightenco/ziggy` | route Laravel di JS/Vue | Presentation |
| `filament/filament` | admin panel (**v5** — terbaru-stabil saat scaffold; butuh Tailwind v4) | Admin |
| `spatie/laravel-medialibrary` | media + aset 3D (GLB/USDZ) | Media3D |
| `spatie/laravel-settings` | typed settings (general/seo/theme/payment) | Settings |
| `spatie/laravel-permission` | RBAC (super-admin/admin/branch-manager) | Branch/Auth |
| `spatie/laravel-translatable` | kolom translatable (i18n-ready) | Catalog/Shared |
| `spatie/laravel-data` | DTO immutable antar layer | Application |
| `spatie/laravel-model-states` | state machine Order & Payment | Ordering/Payment |
| `spatie/laravel-sluggable` | slug otomatis | Catalog |
| `spatie/laravel-sitemap` | sitemap.xml | Seo |
| `moneyphp/money` | aritmetika uang (dibungkus `Shared\ValueObjects\Money`) | Shared |
| `midtrans/midtrans-php` | adapter pembayaran default | Payment |

> **Bulk import/export**: gunakan **Filament Importer/Exporter native** (CSV, queued, progress, error report per-row). Tambahkan `maatwebsite/excel` hanya jika butuh format `.xlsx` lanjutan.

## 3. NPM Packages

| Package | Untuk |
|---|---|
| `@inertiajs/vue3` | Inertia client Vue |
| `vue` (3.x) + `typescript` | UI |
| `vite` + `laravel-vite-plugin` | build/dev |
| `tailwindcss` + `@tailwindcss/forms` | styling |
| `@google/model-viewer` | 3D viewer + AR (web component) |
| `vue-tsc` | type-check Vue+TS |

## 4. Dev Tooling & Quality Gates

| Tool | Peran | Gate |
|---|---|---|
| `pestphp/pest` | testing (unit/feature) | coverage ≥80% |
| `larastan/larastan` (PHPStan) | static analysis | level ≥6 (naikkan bertahap) |
| `laravel/pint` | formatter PHP | wajib hijau di CI |
| `vue-tsc` / ESLint + Prettier | type & lint frontend | wajib hijau |
| Playwright | E2E flow kritikal (checkout, 3D render) | smoke pass |

CI menjalankan: `pint --test` → `larastan` → `pest --coverage` → `vue-tsc` → build Vite → (opsional) E2E.

## 5. Konvensi Koding

### Penamaan & struktur
- Action: kata kerja + objek, `CreateProduct`, `PlaceOrder`, `HandleMidtransWebhook` — satu method publik (`handle`/`__invoke`).
- Repository interface di `Domain/Contracts`, impl `Eloquent*Repository` di `Infrastructure/Repositories`.
- DTO immutable (`readonly`) via `spatie/laravel-data`; tidak mengembalikan array mentah lintas layer.
- Value Object immutable (Money, Sku, Slug) — buat baru, jangan mutasi (lihat aturan global immutability).
- File ≤800 baris, function ≤50 baris, nesting ≤4 level.

### Akses data
- Presentation → Application (Action/Query) → Domain/Repository. **Tidak** ada query Eloquent di controller/Filament untuk business logic non-trivial.
- Lintas-module hanya via Contract atau domain event.

### Error & validasi
- Validasi input di Form Request (storefront) atau saat membentuk DTO.
- Exception domain spesifik (mis. `InsufficientStockException`); pesan ramah di UI, log berkonteks di server.
- Jangan menelan error diam-diam.

### Money & i18n
- Semua nilai uang = integer minor unit + `Money` VO + `currency` eksplisit.
- Teks user-facing produk/kategori translatable; locale default `id`.

### Git
- Conventional commits: `feat|fix|refactor|docs|test|chore|perf|ci: <desc>`.
- Branch sebelum commit di default branch; commit/push hanya saat diminta user.

## 6. Environment Variables (rencana)

```dotenv
APP_NAME="Toko Kiwalan"
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_CURRENCY=IDR

DB_CONNECTION=pgsql
DB_DATABASE=toko_kiwalan

FILESYSTEM_DISK=local        # prod: s3
AWS_BUCKET=                  # aset 3D & media (prod)

QUEUE_CONNECTION=redis       # import/export & webhook async
CACHE_STORE=redis

# Payment — Midtrans (sandbox di dev)
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_MERCHANT_ID=
```

> Tidak ada secret di-hardcode; semua via `.env` + validasi keberadaan saat boot (lihat security rules global). `.env.example` disediakan, key sensitif kosong.

## 7. Referensi Aturan Global

Patuh pada rules global user:
- `coding-style.md` — immutability, file kecil, error handling, input validation.
- `testing.md` — TDD, coverage ≥80%, unit+integration+E2E.
- `security.md` — no hardcoded secret, validasi input, RBAC, webhook signature, rate limiting.
- `git-workflow.md` — conventional commits, PR workflow.

Skill referensi yang relevan saat implementasi: `laravel-patterns`, `laravel-security`, `laravel-tdd`, `laravel-verification`, `api-design`, `e2e-testing`.
