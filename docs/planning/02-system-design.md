# 02 — System Design

## 1. Data Model (ERD ringkas)

```
users ──< role_user >── roles ──< permissions (spatie)
                                  
branches
   │  1
   │
   └──< branch_stock >──┐
                        │ N..1
categories ──< products ──< product_variants
   (parent_id self)         │  (sku, base_price, attributes json)
                            │
products ──< media (polymorphic: images, models3d)   [medialibrary]
products ──1 model3d_settings (auto_rotate, camera, ar_scale)

customers (users) ──< carts ──< cart_items >── product_variants
                         │ branch_id
carts ─(checkout)▶ orders ──< order_items
orders ──1 payments (gateway, gateway_ref, status, payload)

seo_meta (polymorphic: metable → product|category|page)
theme_bundles (manifest json, assets, is_active)
settings (spatie typed groups)
```

### Tabel inti (kolom kunci)

**branches**
| col | tipe | catatan |
|---|---|---|
| id | bigint pk | |
| name | string | |
| code | string unique | kode cabang |
| address | text null | |
| is_active | bool default true | |
| timestamps | | |

**categories**
| col | tipe | catatan |
|---|---|---|
| id | bigint pk | |
| parent_id | bigint null fk→categories | hierarki |
| name | json | translatable (id/en) |
| slug | string unique | |
| position | int | urutan |
| is_active | bool | |

**products**
| col | tipe | catatan |
|---|---|---|
| id | bigint pk | |
| category_id | bigint fk | |
| name | json | translatable |
| description | json null | translatable |
| slug | string unique | |
| status | enum(draft,published,archived) | |
| has_3d | bool default false | flag cepat untuk listing |
| timestamps, soft deletes | | |

**product_variants**
| col | tipe | catatan |
|---|---|---|
| id | bigint pk | |
| product_id | bigint fk | |
| sku | string unique | Value Object `Sku` di domain |
| base_price | bigint | minor unit (rupiah), `Money` VO |
| attributes | json | mis. {"color":"red","size":"L"} |
| is_active | bool | |

**branch_stock** _(resolusi stok & harga per lokasi)_
| col | tipe | catatan |
|---|---|---|
| id | bigint pk | |
| branch_id | bigint fk | |
| product_variant_id | bigint fk | |
| quantity | int default 0 | |
| reserved | int default 0 | stok ditahan saat pending order |
| price_override | bigint null | jika null → pakai variant.base_price |
| unique(branch_id, product_variant_id) | | |

**model3d_settings**
| col | tipe | catatan |
|---|---|---|
| product_id | bigint fk pk | |
| auto_rotate | bool | |
| camera_orbit | string null | param model-viewer |
| ar_enabled | bool | |
| ar_scale | enum(auto,fixed) | |

**carts / cart_items**
- `carts`: id, user_id null, session_id, branch_id, timestamps.
- `cart_items`: id, cart_id, product_variant_id, quantity, unit_price (snapshot `Money`).

**orders**
| col | tipe | catatan |
|---|---|---|
| id | bigint pk | |
| number | string unique | nomor order |
| branch_id | bigint fk | |
| customer_id | bigint null fk | |
| state | string | model-states (lihat §3) |
| subtotal / tax / total | bigint | `Money` |
| currency | string(3) default IDR | |
| customer_snapshot | json | nama/kontak/alamat saat order |
| timestamps | | |

**order_items**: id, order_id, product_variant_id, sku, name_snapshot, unit_price, quantity, line_total.

**payments**
| col | tipe | catatan |
|---|---|---|
| id | bigint pk | |
| order_id | bigint fk | |
| gateway | string | mis. "midtrans" |
| gateway_ref | string null index | transaction id eksternal |
| state | string | pending/paid/failed/expired/refunded |
| amount | bigint | `Money` |
| payload | json | request/response/webhook terakhir |
| timestamps | | |

**seo_meta** (polymorphic): id, metable_type, metable_id, title, description, og_image, canonical, robots, json_ld json.

**theme_bundles**: id, name, version, manifest (json: tokens+settings+seo defaults), is_active, timestamps. Asset (preview/zip) via medialibrary.

## 2. Key Flows

### 2.1 Upload & Render 3D
```
Admin (Filament ProductResource)
  └─ upload GLB (+USDZ opsional) ─▶ UploadModel3D Action
        └─ MediaLibrary: koleksi 'models3d' (validasi mime/size) ─▶ disk (S3/local)
        └─ generate/assign poster (image) ─▶ koleksi 'poster'
        └─ set products.has_3d = true, simpan model3d_settings
Storefront PDP (CatalogController → Inertia)
  └─ props: model_src(GLB url), ios_src(USDZ url), poster, viewer config
        └─ <ProductViewer3D> render <model-viewer> + tombol AR
```

### 2.2 Resolusi Stok & Harga per Branch
```
StockResolver(branchId, variantId):
  row = branch_stock(branch, variant)
  available = row.quantity - row.reserved
  price = row.price_override ?? variant.base_price   // Money
  return { available, price }
```
Dipakai di listing (badge "tersedia di cabang X"), PDP, dan saat add-to-cart.

### 2.3 Checkout → Payment (Midtrans) → Webhook
```
PlaceOrder Action:
  - validasi cart (stok cukup via StockResolver)
  - buat Order(state=pending) + order_items (snapshot harga)
  - reserve stok (branch_stock.reserved += qty)
  - buat Payment(state=pending)
CreateCharge (PaymentGateway = MidtransGateway):
  - createCharge(order) ─▶ Midtrans Snap token / redirect url
  - simpan gateway_ref + payload
Customer bayar di Midtrans ──▶ Webhook (routes/webhooks.php, no CSRF)
HandleWebhook Action:
  - verifikasi signature (signature_key SHA512)  ← security
  - idempoten by gateway_ref + status
  - map status: settlement/capture → Payment.paid → Order.markPaid()
                deny/expire/cancel → Payment.failed → Order.cancel() + release stok
```

### 2.4 Template Bundle Export/Import
```
Export:  ThemeExportService.export(themeId)
  manifest = { version, design_tokens, settings(general,seo,theme), seo_defaults, sample_content_refs }
  zip = manifest.json + assets/ (logo, fonts, preview)  ─▶ download
Import:  ThemeImportService.import(zip)
  - validasi schema & kompatibilitas version
  - restore settings + tokens (transaksional)
  - aktifkan theme bundle
```

### 2.5 Bulk Import/Export Produk
```
Import (Filament Importer):
  CSV ─▶ ProductImporter (kolom: sku, name, category, base_price, stock@branch, ...)
       ─▶ validasi per baris (queued) ─▶ upsert variant + branch_stock
       ─▶ report sukses/gagal per baris + notifikasi
Export (Filament Exporter):
  filter ─▶ ProductExporter ─▶ CSV (queued) ─▶ unduh
```

## 3. State Machines (`spatie/laravel-model-states`)

### Order
```
Pending ──pay──▶ Paid ──fulfill──▶ Fulfilled ──complete──▶ Completed
   │                │
   └──cancel──▶ Cancelled   └──refund──▶ Refunded
```
Transisi tervalidasi (mis. tidak bisa `Pending → Completed` langsung). Setiap transisi memicu domain event + side effect (release/commit stok).

### Payment
```
Pending ──▶ Paid
   ├──▶ Failed
   ├──▶ Expired
   └──(Paid)──▶ Refunded
```

## 4. Route Surface

**Storefront (`routes/web.php`, guard web, Inertia)**
```
GET  /                         Home/landing
GET  /catalog                  listing (filter, search, paginate)
GET  /p/{product:slug}         PDP (3D/AR, JSON-LD)
GET  /cart                     cart
POST /cart/items               add to cart (branch-aware)
PATCH/DELETE /cart/items/{id}  update/remove
GET  /checkout                 form checkout
POST /checkout                 PlaceOrder + CreateCharge
GET  /orders/{number}          status order
GET  /sitemap.xml, /robots.txt SEO
POST /branch/select            pilih branch aktif
```

**Webhook (`routes/webhooks.php`, no CSRF)**
```
POST /webhooks/payment/midtrans   HandleWebhook (signature verify, idempoten)
```

**Admin (Filament panel `/admin`, guard admin)**
```
Resources: Product, Category, Branch, BranchStock, Order, Payment, ThemeBundle
Pages:     GlobalSettings, SeoSettings, PaymentSettings
Actions:   Import Produk (CSV), Export Produk (CSV), Export/Import Template
```

## 5. SEO Design

- **Resolusi meta**: `SeoResolver(entity)` → ambil `seo_meta` per-entity, fallback ke SEO settings global, fallback ke default produk.
- **Sitemap**: `spatie/laravel-sitemap` crawl rute publik / generate dari produk & kategori published; di-cache & regen via scheduler.
- **robots.txt**: dari settings (allow/disallow path).
- **JSON-LD**: `Product` schema di PDP (name, image, offers.price/currency, availability per branch). 3D + structured data = sinyal kaya untuk search/`model-viewer`.
- **hreflang**: disiapkan (i18n-ready) walau v1 hanya `id`.

## 6. Konsistensi & Integritas

- Snapshot harga & nama di `cart_items`/`order_items` (harga tidak berubah retroaktif).
- Stok pakai `reserved` + transaksi DB (lock) saat reserve/commit untuk cegah oversell.
- Webhook idempoten (`gateway_ref` + status guard) — aman terhadap retry Midtrans.
- Money selalu integer minor unit; tidak ada float untuk uang.
