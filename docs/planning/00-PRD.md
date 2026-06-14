# 00 — Product Requirements Document (PRD)

## 1. Visi

Menyediakan **boilerplate e-commerce production-ready & reusable** yang bisa dipakai ulang lintas project, dengan diferensiasi utama pada **3D product viewer + AR**. Boilerplate menekankan kemudahan _tracking_ dan _extensibility_ lewat arsitektur modular SOLID/Clean, sehingga developer bisa fokus pada kustomisasi bisnis, bukan plumbing.

## 2. Tujuan & Non-Tujuan

### Tujuan
- G1 — Katalog + PDP dengan **3D viewer + AR** sebagai pengalaman utama.
- G2 — **Checkout & pembayaran online** yang andal dengan abstraksi gateway pluggable (Midtrans dulu).
- G3 — **Multi-branch**: satu toko, banyak lokasi, stok & harga per lokasi.
- G4 — **Operasional admin cepat** (Filament): product/category/branch management, settings, SEO, import/export.
- G5 — **Reusability**: template selector dengan export/import bundle untuk bootstrap project baru.
- G6 — **Kualitas terlacak**: test ≥80%, static analysis, konvensi konsisten.

### Non-Tujuan (v1)
- Marketplace multi-merchant / multi-tenant penuh (di-_skip_; arsitektur tetap tidak menutup jalan ke sana).
- Multi-currency + multi-language penuh (hanya _i18n-ready_, default IDR + Bahasa).
- Mobile native app (storefront web responsif sudah cukup; AR via browser).
- Fitur lanjutan (loyalty, subscription, marketplace shipping aggregator) — masuk roadmap future.

## 3. Persona

| Persona | Kebutuhan |
|---|---|
| **Pembeli (Customer)** | Browse katalog, lihat produk dalam 3D/AR, pilih cabang/lokasi, checkout & bayar online. |
| **Admin Toko** | Kelola produk, varian, kategori, stok per cabang, settings global & SEO, lihat order. |
| **Branch Manager** | Akses terbatas ke stok & order cabangnya (RBAC). |
| **Developer (pengguna boilerplate)** | Clone boilerplate, import template bundle, extend module dengan pola konsisten. |

## 4. Functional Requirements (per module, prioritas MoSCoW)

### Catalog (Must)
- FR-C1 — CRUD Category (hierarki/parent-child, slug, translatable name).
- FR-C2 — CRUD Product (translatable name/description, status draft/published, kategori, media).
- FR-C3 — Product Variant (SKU unik, base price, atribut mis. warna/ukuran).
- FR-C4 — Storefront: listing katalog (filter kategori, search, pagination) + PDP.

### Media3D (Must)
- FR-M1 — Upload aset 3D (GLB/GLTF wajib, USDZ opsional untuk AR iOS) per produk/varian.
- FR-M2 — Upload/auto-poster (thumbnail) & gambar produk biasa.
- FR-M3 — PDP merender `<model-viewer>` + tombol AR (Scene Viewer/Quick Look).
- FR-M4 — Konfigurasi viewer per produk (auto-rotate, camera, AR scale). _(Should)_

### Branch (Must)
- FR-B1 — CRUD Branch (nama, kode, alamat, status aktif).
- FR-B2 — Stok per varian per branch (`branch_stock`), dengan opsi `price_override`.
- FR-B3 — Storefront: pilih/deteksi branch; ketersediaan & harga di-resolve per branch.
- FR-B4 — RBAC: branch manager hanya akses data cabangnya. _(Should)_

### Ordering & Payment (Must)
- FR-O1 — Cart terikat branch (snapshot harga saat add-to-cart).
- FR-O2 — Checkout: data pembeli, ringkasan, buat Order.
- FR-O3 — Order state machine: `pending → paid → fulfilled → completed`, + `cancelled/refunded`.
- FR-P1 — Abstraksi `PaymentGateway` (Strategy) + adapter **Midtrans** (Snap/VA/e-wallet/QRIS).
- FR-P2 — Webhook handler idempoten untuk update status pembayaran.
- FR-P3 — Tambah gateway baru tanpa mengubah core (OCP). _(Should: Xendit/Stripe sebagai contoh)_

### Settings & SEO (Must)
- FR-S1 — Global settings ter-tipe (nama toko, kontak, mata uang, pajak, dsb).
- FR-S2 — SEO settings global + per-entity (title, meta description, OG image, canonical, robots).
- FR-S3 — Sitemap.xml & robots.txt ter-generate.
- FR-S4 — JSON-LD `Product` structured data di PDP (sinergi dengan 3D).

### Import/Export (Must)
- FR-I1 — Bulk import produk via CSV (mapping kolom, validasi per baris, report error).
- FR-I2 — Bulk export produk via CSV.
- FR-I3 — Proses ter-queue dengan progress & notifikasi.

### Theming / Template Selector (Should)
- FR-T1 — Runtime theme switcher (design tokens: warna, font, layout).
- FR-T2 — Export template bundle (manifest JSON + asset zip): tokens + settings + SEO defaults + sample content.
- FR-T3 — Import template bundle ke instance baru (validasi versi, restore).

### Auth & RBAC (Must)
- FR-A1 — Auth customer (storefront) & admin (Filament panel) terpisah.
- FR-A2 — Role & permission (super-admin, admin, branch-manager).

## 5. Non-Functional Requirements

- NFR-1 **Maintainability** — modular monolith, SOLID, file ≤800 baris, function ≤50 baris.
- NFR-2 **Testability** — coverage ≥80% (unit + integration + E2E flow kritikal), TDD.
- NFR-3 **Performance** — listing katalog < 300ms p95 (dengan cache); lazy-load aset 3D.
- NFR-4 **Security** — validasi input di boundary, no hardcoded secret, RBAC, webhook signature verify, CSRF, rate limiting.
- NFR-5 **Portability** — filesystem disk abstraksi (lokal/S3) untuk aset 3D & media.
- NFR-6 **Observability** — logging error berkonteks, audit trail untuk perubahan stok/order. _(Should)_
- NFR-7 **i18n-ready** — kolom translatable & money value object dari awal.

## 6. Success Criteria

1. `php artisan serve` + storefront menampilkan katalog dengan PDP 3D/AR berfungsi.
2. Flow end-to-end: pilih branch → add to cart → checkout → bayar (Midtrans sandbox) → order `paid`.
3. Admin bisa bulk-import produk dari CSV dan export kembali.
4. Template bundle bisa di-export lalu di-import ke instance bersih dan menghasilkan tampilan setara.
5. CI hijau: Pint (format), Larastan (level disepakati), Pest (coverage ≥80%).

## 7. Out of Scope / Future Roadmap

- Wishlist, review & rating, voucher/diskon engine, flash sale.
- Multi-tenant/marketplace, multi-currency real-time, multi-warehouse fulfillment routing.
- Shipping rate aggregator (RajaOngkir/Biteship) — _kandidat kuat fase berikutnya_.
- Notifikasi (email/WA) transaksional lanjutan.
