# Modules

Bounded-context modules for the modular monolith. See
[../../docs/planning/01-architecture.md](../../docs/planning/01-architecture.md)
for the full architecture and dependency rules.

## Layout per module

```
app/Modules/<Context>/
├── Domain/            # business core (Models, ValueObjects, Events, Contracts)
├── Application/       # use cases (Actions, DTO, Queries)
├── Infrastructure/    # adapters (Repositories, Gateways) + <Context>ServiceProvider
├── Presentation/      # entry surfaces (Filament resources, Http controllers/requests)
└── Database/Migrations/   # module-owned migrations, auto-loaded by its provider
```

## Adding a module

1. Create the directory skeleton above under `app/Modules/<Context>`.
2. Add `Infrastructure/<Context>ServiceProvider` extending
   `App\Modules\Shared\Infrastructure\ModuleServiceProvider` — implement
   `modulePath()`, and override `registerBindings()` / `bootModule()` as needed.
   (`SharedServiceProvider` is the reference template.)
3. Register the provider in `bootstrap/providers.php`.

Classes autoload via the root `App\\ → app/` PSR-4 mapping, so
`App\Modules\<Context>\...` resolves to `app/Modules/<Context>/...` with no extra
composer configuration.

## Dependency rule

`Presentation → Application → Domain ← Infrastructure`. Domain imports nothing
outward; cross-module access goes only through another module's `Domain/Contracts`
or a domain event — never its models/tables directly.
