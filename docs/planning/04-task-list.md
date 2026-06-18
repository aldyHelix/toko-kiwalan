# 04 — Task List

> Breakdown implementasi per fase. Setiap fase punya **tujuan**, **deliverable bertanda checkbox**, dan **acceptance criteria (AC)** yang harus hijau sebelum lanjut. Urutan fase mengikuti graf dependensi modul di [01-architecture.md](./01-architecture.md) §3.

## Cara membaca

- **AC** = syarat selesai fase (Definition of Done). Tidak boleh pindah fase jika AC belum hijau.
- **Quality gate per fase** (berlaku di semua fase setelah ada kode): `pint --test` → `larastan` → `pest --coverage` (≥80% untuk kode fase tsb) → `vue-tsc` (jika ada perubahan FE). Lihat [03-tech-stack.md](./03-tech-stack.md) §4.
- **TDD wajib** untuk business rule (Action, Domain service, state machine, resolver). CRUD Filament sederhana boleh test feature ringan.
- **Commit** per task selesai (conventional commits). Branch sebelum commit; push/PR hanya saat diminta user.

## Peta fase → modul

| Fase | Fokus | Modul utama | Depends on fase |
|---|---|---|---|
| 0 | Scaffold & tooling | — (infra) | — |
| 1 | Fondasi domain, settings, auth/RBAC | Shared, Settings, Auth | 0 |
| 2 | Cabang & lokasi | Branch | 1 |
| 3 | Katalog & resolusi stok/harga | Catalog | 2 |
| 4 | 3D viewer + AR | Media3D | 3 |
| 5 | Cart, checkout, order state machine | Ordering | 3 |
| 6 | Abstraksi pembayaran + Midtrans + webhook | Payment | 5 |
| 7 | SEO, Theming, Import/Export | Seo, Theming, ImportExport | 3, (6) |
| 8 | Demo data, seeders, E2E, hardening, rilis | semua | 1–7 |

---

## Fase 0 — Scaffold & Tooling

**Tujuan:** kerangka kerja siap pakai — Laravel 12 boot, storefront Inertia/Vue, panel admin Filament, modul autoloading, quality gate hijau di kondisi kosong.

- [ ] Install **Laravel 12** + PHP 8.3, set `.env` & `.env.example` (lihat [03-tech-stack.md](./03-tech-stack.md) §6); validasi env wajib saat boot.
- [ ] Verifikasi **versi terbaru-stabil** tiap package sebelum require (Filament v3/v4, dll) lalu kunci di `composer.json`.
- [ ] Setup **Inertia 2 + Vue 3 + TypeScript + Tailwind** + Vite; `tightenco/ziggy` untuk route di JS.
- [ ] Install **Filament** sebagai panel `/admin` dengan guard `admin` terpisah dari guard `web`.
- [ ] Buat struktur **`app/Modules/<Context>`** (Domain/Application/Infrastructure/Presentation) + PSR-4 autoload ([01-architecture.md](./01-architecture.md) §7).
- [ ] Pola **module ServiceProvider**: `loadMigrationsFrom`, binding, route, config — daftarkan di `bootstrap/providers.php`. Buat 1 contoh (`SharedServiceProvider`) sebagai template.
- [ ] Setup tooling: **Pint**, **Larastan** (level 6 awal), **Pest** + coverage, **vue-tsc** + ESLint/Prettier.
- [ ] **CI pipeline** menjalankan urutan gate; hijau di repo kosong.
- [ ] `routes/web.php` (storefront) + `routes/webhooks.php` (no-CSRF, stub) terdaftar.

**AC:**
1. `php artisan serve` jalan; halaman `/` (Inertia) render tanpa error.
2. `/admin` menampilkan login Filament (guard `admin`).
3. `composer test`/CI hijau: Pint, Larastan L6, Pest, vue-tsc.
4. Satu module dummy ter-autoload & provider-nya teregistrasi (bukti pola modular bekerja).

---

## Fase 1 — Shared, Settings, Auth & RBAC

**Tujuan:** fondasi lintas-modul: Value Object uang/identitas, base contract repo & DTO, typed settings, dan autentikasi+otorisasi dua guard.

### Shared
- [x] **Value Objects immutable**: `Money` (bungkus `moneyphp/money`, integer minor unit, currency eksplisit), `Sku`, `Slug`. Unit test aritmetika & equality.
- [x] **Base contracts**: `Repository` interface dasar (findAll/findById/create/update/delete) + base **DTO** (`spatie/laravel-data`, `readonly`).
- [x] Helper tipis di `app/Support` (hindari logika bisnis) — `App\Support\Rupiah`.

### Settings
- [x] Typed settings groups via `spatie/laravel-settings`: **General** (nama toko, kontak, currency, pajak), **Seo**, **Theme**, **Payment**. Di-cache (`SETTINGS_CACHE_ENABLED`).
- [x] Filament **Pages**: GlobalSettings, SeoSettings, PaymentSettings (form ter-tipe), didaftarkan via `SettingsPlugin` (pola plugin per-modul).

### Auth & RBAC
- [x] Guard **`web`** (customer) & **`admin`** terpisah; model `User` + `Admin` (pemisahan credential store).
- [x] `spatie/laravel-permission`: role **super-admin, admin, branch-manager** + permission map (`App\Support\Rbac`, guard `admin`); super-admin via `Gate::before`.
- [x] Auth storefront (register/login/logout customer, hand-rolled) & login panel admin (Filament, gated by role).
- [x] Skeleton **branch-scoping**: pure `BranchAccessPolicy` (decision logic). Eloquent global scope/policy disempurnakan di Fase 2.

**AC:**
1. `Money` menolak operasi beda currency; tidak ada float untuk uang (unit test bukti).
2. Settings tersimpan, ter-cache, terbaca di Filament page & domain.
3. Customer bisa register/login (`web`); admin login (`admin`); role ter-assign & permission ter-cek.
4. Coverage ≥80% untuk Shared & Settings; Pint/Larastan hijau.

---

## Fase 2 — Branch

**Tujuan:** satu toko, banyak lokasi; fondasi resolusi per-cabang & RBAC branch-manager.

- [x] Migration + model **`branches`** (name, code unique, address, is_active).
- [x] `BranchRepository` contract + `EloquentBranchRepository`; binding di provider.
- [x] Actions: `CreateBranch`, `UpdateBranch`, `ToggleBranchActive` (TDD).
- [x] Filament **BranchResource** (CRUD) — create/update delegasi ke Action, table action toggle aktif.
- [x] Storefront: **pilih/deteksi branch** aktif (`POST /branch/select`), simpan di session; expose ke Inertia shared props (`branch.active`/`branch.available`) + `BranchSwitcher.vue`.
- [x] **RBAC branch-scope**: branch-manager hanya akses data cabangnya (`BranchPolicy` + `BranchScope` global scope, kolom dikonfigurasi agar reusable), super-admin/admin lihat semua. `admins.branch_id` + `Admin::branchId()/roleNames()`.

**AC:**
1. Admin CRUD branch via Filament; `code` unik tervalidasi.
2. Storefront bisa memilih branch & branch aktif persist antar request.
3. Branch-manager tidak bisa mengakses cabang lain (test policy bukti).
4. Quality gate hijau; coverage modul ≥80%.

---

## Fase 3 — Catalog (Category, Product, Variant, Stock)

**Tujuan:** katalog inti + resolusi stok/harga per cabang + storefront listing & PDP (tanpa 3D dulu).

### Domain & data
- [ ] Migrations + model: **categories** (parent_id self, name json translatable, slug, position, is_active), **products** (translatable name/desc, slug, status enum, has_3d, soft delete), **product_variants** (sku unique→`Sku`, base_price→`Money`, attributes json), **branch_stock** (quantity, reserved, price_override, unique(branch,variant)).
- [ ] `spatie/laravel-translatable` (name/desc), `spatie/laravel-sluggable` (slug).
- [ ] Repos + contracts: `CategoryRepository`, `ProductRepository`, `VariantRepository`, `StockRepository` (pisahkan `StockReader`/`StockWriter` — ISP).
- [ ] **`StockResolver(branchId, variantId)`** → `{ available = quantity - reserved, price = price_override ?? base_price }`. TDD ketat (ini berbayar).
- [ ] Actions: `CreateProduct`, `UpdateProduct`, `AddVariant`, `SetBranchStock`, kategori CRUD.

### Presentation
- [ ] Filament Resources: **CategoryResource, ProductResource (+ relation variants), BranchStockResource**.
- [ ] Storefront `CatalogController` → Inertia: **listing** `/catalog` (filter kategori, search, pagination) + **PDP** `/p/{product:slug}` (resolusi stok/harga per branch aktif). Cache listing (target <300ms p95).

**AC:**
1. Admin buat kategori (hierarki) + produk + varian + set stok per cabang.
2. `StockResolver` benar untuk: stok cukup, reserved, price_override null vs terisi (unit test).
3. Storefront listing (filter/search/paginate) & PDP menampilkan ketersediaan + harga **sesuai branch aktif**.
4. Tidak ada query Eloquent business-logic di controller (lewat Action/Query). Coverage ≥80%; gate hijau.

---

## Fase 4 — Media3D (3D Viewer + AR)

**Tujuan:** diferensiasi utama — upload aset 3D & render `<model-viewer>` + AR di PDP.

- [ ] `spatie/laravel-medialibrary`: koleksi **`images`**, **`models3d`** (GLB/GLTF wajib, USDZ opsional), **`poster`**. Validasi mime & ukuran; disk abstraksi (local dev / S3 prod).
- [ ] Migration + model **`model3d_settings`** (auto_rotate, camera_orbit, ar_enabled, ar_scale enum) — 1:1 produk.
- [ ] Action **`UploadModel3D`**: simpan GLB/USDZ, assign/auto poster, set `products.has_3d = true`, simpan settings.
- [ ] Filament: panel upload 3D + form viewer config di ProductResource.
- [ ] FE **`ProductViewer3D.vue`** (`@google/model-viewer`): props `model_src`, `ios_src`, `poster`, viewer config; tombol AR (Scene Viewer / Quick Look). **Lazy-load** aset.
- [ ] PDP merender viewer bila `has_3d`, fallback gambar bila tidak.

**AC:**
1. Admin upload GLB (+USDZ opsional); poster ter-set; `has_3d` aktif.
2. PDP merender `<model-viewer>` + tombol AR; non-3D fallback ke galeri gambar.
3. Validasi mime/size menolak file invalid (test feature).
4. Aset 3D lazy-load (tidak block initial render). Gate hijau; coverage ≥80%.

---

## Fase 5 — Ordering (Cart, Checkout, Order State Machine)

**Tujuan:** keranjang branch-aware → order dengan state machine tervalidasi & stok ter-reserve.

- [ ] Migrations + model: **carts** (user_id null, session_id, branch_id), **cart_items** (variant, qty, unit_price snapshot `Money`), **orders** (number unique, branch_id, customer_id, state, subtotal/tax/total `Money`, currency, customer_snapshot json), **order_items** (snapshot sku/name/unit_price/qty/line_total).
- [ ] Cart Actions: `AddToCart` (branch-aware, snapshot harga via `StockResolver`), `UpdateCartItem`, `RemoveCartItem`. Cart terikat satu branch.
- [ ] **`spatie/laravel-model-states`** untuk Order: `Pending→Paid→Fulfilled→Completed`, + `Cancelled/Refunded`; transisi invalid ditolak; tiap transisi memicu domain event + side-effect stok.
- [ ] Action **`PlaceOrder`**: validasi stok, buat Order(pending) + items (snapshot), **reserve** stok (`branch_stock.reserved += qty`) dalam transaksi DB (lock) — cegah oversell.
- [ ] Storefront: `/cart`, `/checkout` (form pembeli + ringkasan), `POST /checkout` → PlaceOrder; `/orders/{number}` status.
- [ ] Domain exception (mis. `InsufficientStockException`) — pesan ramah di UI, log berkonteks.

**AC:**
1. Add-to-cart menolak qty melebihi `available`; harga ter-snapshot saat add.
2. `PlaceOrder` membuat order `pending` + reserve stok atomik; oversell tidak terjadi di test konkuren.
3. Transisi state invalid (mis. `Pending→Completed`) dilempar exception (test bukti).
4. Coverage ≥80% untuk Ordering; gate hijau.

---

## Fase 6 — Payment (Abstraksi + Midtrans + Webhook)

**Tujuan:** pembayaran online andal via abstraksi pluggable; Midtrans sebagai adapter default; webhook aman & idempoten.

- [ ] Migration + model **`payments`** (order_id, gateway, gateway_ref index, state, amount `Money`, payload json).
- [ ] **`spatie/laravel-model-states`** Payment: `Pending→Paid/Failed/Expired`, `Paid→Refunded`.
- [ ] Contract **`PaymentGateway`** (Strategy) — `createCharge(order)`, `verifyWebhook(payload)`, `mapStatus(...)`. Kecil & fokus (OCP/ISP).
- [ ] Adapter **`MidtransGateway`** (`midtrans/midtrans-php`): Snap/VA/e-wallet/QRIS; simpan `gateway_ref` + payload.
- [ ] Action `CreateCharge` (dipanggil setelah PlaceOrder) → token/redirect Midtrans.
- [ ] Action **`HandleMidtransWebhook`** di `routes/webhooks.php` (no CSRF): **verifikasi signature SHA512** → **idempoten** (guard `gateway_ref` + status) → map status: settlement/capture→`Payment.paid`→`Order.markPaid`; deny/expire/cancel→`Payment.failed`→`Order.cancel` + **release stok**.
- [ ] **Rate limiting** pada endpoint webhook & checkout.
- [ ] (Should) Adapter contoh kedua (Xendit/Stripe **stub**) untuk membuktikan OCP — tanpa edit core.

**AC:**
1. Checkout sandbox: `pending → paid` end-to-end (Midtrans sandbox); order jadi `Paid`.
2. Webhook menolak signature invalid; retry Midtrans tidak menggandakan efek (idempoten — test bukti).
3. Pembayaran gagal/expire → order `cancelled` + stok ter-release.
4. Menambah gateway baru tidak mengubah core Payment (bukti via stub). Coverage ≥80%; gate hijau; secret hanya via `.env`.

---

## Fase 7 — SEO, Theming, Import/Export

**Tujuan:** kelengkapan boilerplate yang membuat reusable & search-friendly.

### Seo
- [ ] Migration + model **`seo_meta`** polymorphic (metable, title, description, og_image, canonical, robots, json_ld json).
- [ ] **`SeoResolver(entity)`**: per-entity → fallback SEO settings global → fallback default produk.
- [ ] `spatie/laravel-sitemap`: `/sitemap.xml` (produk & kategori published), cache + regen via scheduler; `/robots.txt` dari settings.
- [ ] **JSON-LD `Product`** di PDP (name, image, offers.price/currency, availability per branch). hreflang disiapkan (i18n-ready, v1 hanya `id`).

### Theming
- [ ] Migration + model **`theme_bundles`** (name, version, manifest json: tokens+settings+seo defaults, is_active); asset via medialibrary.
- [ ] Runtime **theme switcher** (design tokens: warna/font/layout) ke storefront.
- [ ] **`ThemeExportService.export`**: manifest.json + assets zip (download).
- [ ] **`ThemeImportService.import`**: validasi schema/versi → restore settings+tokens transaksional → aktifkan bundle.

### ImportExport
- [ ] **Filament Importer** `ProductImporter` (CSV: sku, name, category, base_price, stock@branch, …): validasi per baris, **queued**, upsert variant + branch_stock, report sukses/gagal + notifikasi.
- [ ] **Filament Exporter** `ProductExporter` (CSV, queued, filter).

**AC:**
1. PDP punya `<meta>` & JSON-LD valid; `/sitemap.xml` & `/robots.txt` ter-generate.
2. Export template bundle lalu import ke instance bersih → tampilan setara (AC sukses #4 PRD).
3. Bulk import CSV: baris valid ter-upsert, baris invalid dilaporkan tanpa gagal total; export menghasilkan CSV terbaca.
4. Proses import/export berjalan ter-queue dengan progress. Coverage ≥80%; gate hijau.

---

## Fase 8 — Demo Data, Seeders, E2E & Hardening

**Tujuan:** konten contoh, pengujian end-to-end, dan finalisasi kualitas untuk rilis boilerplate.

> Sesuai catatan README: **seeder & demo data di-defer ke sini**. Skema & factory sudah ada sejak fase masing-masing; di fase ini diisi konten contoh.

- [ ] **Factories** lengkap (jika belum) + **Seeders**: branches, categories, products+variants (sebagian `has_3d` dengan sample GLB), branch_stock, role/permission, settings default, 1 theme bundle default.
- [ ] Sample aset 3D (GLB/USDZ) + poster untuk demo PDP.
- [ ] **E2E Playwright** flow kritikal: (a) pilih branch → add to cart → checkout → bayar sandbox → order `paid`; (b) PDP render 3D + tombol AR muncul; (c) admin bulk-import CSV lalu export.
- [ ] **Hardening**: review security (RBAC, webhook signature, rate limit, no hardcoded secret, validasi boundary) via `security-reviewer`; audit trail perubahan stok/order (Should).
- [ ] **Coverage global ≥80%**; naikkan Larastan ke level disepakati bila stabil.
- [ ] Finalisasi docs: `README` utama proyek, update `docs/planning/README.md` status, catatan deploy & env.

**AC (selaras Success Criteria PRD §6):**
1. `php artisan serve` + storefront menampilkan katalog dengan PDP 3D/AR berfungsi (seeded).
2. Flow E2E: branch → cart → checkout → Midtrans sandbox → order `paid` hijau.
3. Admin bulk-import produk dari CSV & export kembali.
4. Template bundle export → import ke instance bersih → tampilan setara.
5. CI hijau penuh: Pint, Larastan, Pest (coverage ≥80%), vue-tsc, build Vite, E2E smoke.

---

## Catatan lintas-fase

- **Definition of Done global per task:** kode + test + gate hijau + commit conventional. Tidak ada task "selesai" tanpa test untuk business rule.
- **Urutan kritikal:** Fase 5 (Ordering) **harus** sebelum Fase 6 (Payment). Fase 4 (Media3D) & Fase 5 boleh paralel setelah Fase 3 (keduanya hanya depend Catalog).
- **i18n & Money** ditegakkan sejak Fase 1; jangan menambah kolom uang non-`Money` atau teks user-facing non-translatable di fase manapun.
- **Boundary modul:** akses lintas-modul hanya via Contract atau domain event — dilarang query tabel modul lain langsung (lihat [01-architecture.md](./01-architecture.md) §2).
