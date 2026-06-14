<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure;

use App\Modules\Shared\Domain\Contracts\Clock;

/**
 * Service provider for the Shared module. Copy this as the template for every
 * other module's `Infrastructure/<Context>ServiceProvider` and register it in
 * bootstrap/providers.php.
 */
class SharedServiceProvider extends ModuleServiceProvider
{
    protected function modulePath(): string
    {
        // app/Modules/Shared/Infrastructure → app/Modules/Shared
        return dirname(__DIR__);
    }

    protected function registerBindings(): void
    {
        $this->app->bind(Clock::class, SystemClock::class);
    }
}
