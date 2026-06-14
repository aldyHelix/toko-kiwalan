# 01 — Architecture

## 1. Gaya Arsitektur

**Modular Monolith** dengan **bounded contexts**. Satu codebase, satu deploy, tapi terbagi jadi module berdaulat dengan boundary jelas. Ini pilihan pragmatis: keuntungan organisasi & testability dari Clean Architecture, tanpa overhead microservices atau mapping hexagonal penuh yang berlebihan untuk skala boilerplate.

### Prinsip pemandu
- **SRP** — satu use case = satu Action class.
- **OCP** — extension point lewat interface + Strategy (payment gateway, theme renderer, importer).
- **DIP** — layer atas bergantung pada **interface**, bukan implementasi Eloquent; binding di service provider.
- **Immutability** — DTO & Value Object immutable; hindari mutasi in-place (lihat coding-style global).
- **High cohesion, low coupling** — module berkomunikasi via contract/event, bukan akses tabel langsung lintas module.

## 2. Layering per Module

```
app/Modules/<Context>/
├── Domain/            # Inti bisnis, tanpa dependensi framework jika memungkinkan
│   ├── Models/        #   Eloquent model (dipakai sebagai entity — pragmatis)
│   ├── ValueObjects/  #   Money, Sku, Slug, dsb (immutable)
│   ├── Events/        #   Domain events (mis. OrderPaid)
│   └── Contracts/     #   Interface repository & service (port)
├── Application/       # Orkestrasi use case
│   ├── Actions/       #   1 class = 1 use case (CreateProduct, PlaceOrder)
│   ├── DTO/           #   spatie/laravel-data, immutable
│   └── Queries/       #   Read model / query object (opsional, CQRS-lite)
├── Infrastructure/    # Detail teknis (adapter)
│   ├── Repositories/  #   EloquentXxxRepository implements Contracts\XxxRepository
│   ├── Gateways/      #   Adapter eksternal (MidtransGateway)
│   └── <Context>ServiceProvider.php   # binding interface→impl, register routes/config
└── Presentation/      # Antarmuka masuk (driver)
    ├── Filament/      #   Resources/Pages untuk admin
    └── Http/          #   Controllers/Requests untuk storefront (Inertia)
```

### Aturan dependensi (boleh menunjuk ke dalam, tidak keluar)
```
Presentation ──▶ Application ──▶ Domain ◀── Infrastructure
                                   ▲
                          (Infrastructure mengimplementasi Domain\Contracts)
```
- `Domain` **tidak** mengimpor `Application`/`Infrastructure`/`Presentation`.
- `Presentation` **tidak** memanggil Eloquent/Repository langsung — lewat `Action`/`Query`.
- Akses lintas-module hanya via **Contracts** module lain atau **domain event**, tidak lewat model/tabel langsung.

> **Sikap pragmatis (penting):** Eloquent model berperan ganda sebagai entity untuk menghindari mapping berlebih. Yang non-negotiable: **business rule hidup di Action/Domain service** dan **akses data lewat repository interface** di titik yang berbayar (payment, order state, stock). Untuk CRUD admin sederhana via Filament, Resource boleh memakai model langsung — SOLID diterapkan di mana ia memberi nilai, bukan dogmatis.

## 3. Module Map & Tanggung Jawab

| Module | Tanggung jawab | Depends on |
|---|---|---|
| **Shared** | Value Object umum (Money, Sku, Slug), base Repository contract, base DTO, helper. | — |
| **Settings** | Typed settings (general, seo, theme, payment) via spatie/laravel-settings. | Shared |
| **Branch** | Branch/lokasi, RBAC scope per branch. | Shared, Settings |
| **Catalog** | Category, Product, Variant, Attribute, branch_stock (resolusi stok/harga). | Shared, Branch |
| **Media3D** | Aset 3D (GLB/USDZ) + image via medialibrary, viewer config, AR. | Shared, Catalog |
| **Ordering** | Cart, Order, checkout, order state machine. | Shared, Catalog, Branch |
| **Payment** | `PaymentGateway` contract + adapter (Midtrans), webhook, payment state. | Shared, Ordering |
| **Seo** | seo_meta polymorphic, sitemap, robots, JSON-LD. | Shared, Settings, Catalog |
| **Theming** | Theme bundle (design tokens), export/import service. | Shared, Settings |
| **ImportExport** | Bulk import/export produk (Filament Importer/Exporter). | Shared, Catalog |

Diagram dependensi (acyclic):
```
Shared ◀─ Settings ◀─ Seo
   ▲          ▲
   ├── Branch ◀── Catalog ◀── Media3D
   │              ▲   ▲
   │           Ordering ◀── Payment
   │              ▲
   └── Theming    └── ImportExport
```

## 4. Admin (Filament) vs Storefront (Inertia/Vue)

Dua _entry surface_ yang berbagi domain yang sama:

| | Admin | Storefront |
|---|---|---|
| Stack | Filament (Livewire 3) | Inertia 2 + Vue 3 + TS |
| Route prefix | `/admin` (panel) | `/` |
| Auth guard | `admin` | `web` (customer) |
| Akses data | Filament Resource → Action/Repository | Controller → Action/Query |
| Kapan dipakai | Operasional internal (CRUD, settings, import/export) | Pengalaman pembeli (katalog, 3D, checkout) |

Keduanya **coexist tanpa konflik**: Filament berjalan di panel & route sendiri; storefront di route Inertia. Keduanya memanggil layer **Application** yang sama → tidak ada duplikasi business rule.

## 5. Cross-Cutting Concerns

- **Auth & RBAC** — guard `web` (customer) & `admin`; role/permission via `spatie/laravel-permission` (super-admin, admin, branch-manager). Branch-scoping via global scope/policy.
- **Settings** — `spatie/laravel-settings` (typed classes per group), di-cache.
- **Media & 3D** — `spatie/laravel-medialibrary`, disk abstraksi (lokal di dev, S3-compatible di prod). Koleksi `models3d` (GLB/USDZ) terpisah dari `images`.
- **Money** — `Shared/ValueObjects/Money` (wrapper `moneyphp/money`), disimpan integer minor unit; default IDR.
- **i18n** — `spatie/laravel-translatable` untuk kolom translatable (name/description); locale default `id`.
- **DTO** — `spatie/laravel-data` antar layer; immutable.
- **Events** — domain event (`OrderPaid`, `StockReserved`) untuk decoupling lintas module.
- **Error handling** — validasi di Form Request / DTO factory; exception domain spesifik; tidak menelan error diam-diam.

## 6. Struktur Direktori (top-level)

```
toko-kiwalan/
├── app/
│   ├── Modules/            # bounded contexts (lihat §2)
│   ├── Models/             # hanya model lintas-module umum (User) bila perlu
│   ├── Providers/          # AppServiceProvider, FilamentServiceProvider
│   └── Support/            # helper global tipis
├── resources/
│   ├── js/
│   │   ├── Pages/          # halaman Inertia (Vue)
│   │   ├── Components/      # komponen Vue (termasuk ProductViewer3D.vue)
│   │   └── app.ts
│   └── css/
├── routes/
│   ├── web.php             # storefront (Inertia)
│   └── webhooks.php        # payment webhook (tanpa CSRF, dengan signature verify)
├── tests/                  # Pest (Unit, Feature, plus E2E terpisah)
├── docs/planning/          # dokumen ini
└── config/
```

## 7. Module Autoloading (PSR-4)

`composer.json`:
```jsonc
"autoload": {
  "psr-4": {
    "App\\": "app/",
    "App\\Modules\\": "app/Modules/",
    "Database\\Factories\\": "database/factories/",
    "Database\\Seeders\\": "database/seeders/"
  }
}
```
Tiap module mendaftarkan `Infrastructure/<Context>ServiceProvider` (binding, route, config, migration path) di `bootstrap/providers.php`. Migration per module disimpan di `app/Modules/<Context>/Database/Migrations` dan di-load oleh service provider-nya (`loadMigrationsFrom`).

## 8. Catatan SOLID — di mana diterapkan & kenapa

| Prinsip | Diterapkan di | Alasan |
|---|---|---|
| **S** | Action per use case | Perubahan satu use case tidak mengganggu lainnya; mudah di-test & di-track. |
| **O** | `PaymentGateway`, `ThemeExporter`, importer | Tambah gateway/format tanpa edit core. |
| **L** | Implementasi repository & gateway taat kontrak | Bisa di-swap (real/fake) di test tanpa breakage. |
| **I** | Contract kecil & fokus (mis. `StockReader` vs `StockWriter`) | Konsumer tidak bergantung method yang tidak dipakai. |
| **D** | Binding interface→impl di ServiceProvider | Domain/Application bebas dari detail Eloquent/HTTP. |
