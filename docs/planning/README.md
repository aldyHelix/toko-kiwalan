# Toko Kiwalan — Planning Docs

Boilerplate e-commerce **Laravel 12** dengan fitur unggulan **3D product viewer + AR**, katalog, pembayaran online, multi-branch, SEO, template selector (export/import), dan bulk import/export produk. Dibangun dengan prinsip **SOLID + Clean Architecture** (modular monolith) agar mudah di-track & di-extend.

## Index

| Doc | Isi |
|---|---|
| [00-PRD.md](./00-PRD.md) | Product Requirements — visi, persona, functional & non-functional requirements, scope |
| [01-architecture.md](./01-architecture.md) | Architecture — modular monolith, layering SOLID/Clean, module map, dependency rules |
| [02-system-design.md](./02-system-design.md) | System Design — data model/ERD, key flows, state machines, route surface |
| [03-tech-stack.md](./03-tech-stack.md) | Tech Stack — versi, package, tooling, konvensi koding, env vars |
| [04-task-list.md](./04-task-list.md) | Task List — breakdown per fase dengan acceptance criteria |

## Keputusan Arsitektur (Locked)

| Aspek | Keputusan |
|---|---|
| Backend | Laravel 12 + PHP 8.3, modular monolith |
| Storefront | Inertia 2 + Vue 3 + TypeScript + Tailwind |
| Admin | Filament (panel terpisah, coexist dengan storefront) |
| 3D/AR | Google `<model-viewer>` — GLB/GLTF + USDZ |
| Payment | Abstraksi pluggable (Strategy), Midtrans sebagai adapter default |
| Branch | Single store, multi-lokasi (stok & harga override per cabang) |
| Template | Full bundle export/import (manifest JSON + asset zip) |
| i18n | IDR + Bahasa, skema translatable & currency-aware dari awal |

## Status

- [x] Brainstorming & keputusan arsitektur
- [x] Planning docs (dokumen ini)
- [x] Fase 0 — Scaffold (Laravel 12 · Inertia 2/Vue 3/TS/Tailwind 4 · Filament v5 · modul autoloading · gate hijau)
- [x] Fase 1 — Shared (Money/Sku/Slug · base Repository/DTO) · Settings (4 typed groups + Filament pages) · Auth & RBAC (dua guard, spatie/permission, customer auth, branch-scope skeleton)
- [ ] Fase 2–8 — lihat [04-task-list.md](./04-task-list.md)

> **Catatan:** Database seeder & demo data **di-defer** ke Fase 8 (lihat task list). Skema & factory disiapkan lebih dulu; seeder konten contoh dikerjakan belakangan atas permintaan.
