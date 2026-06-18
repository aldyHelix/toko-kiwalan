<?php

declare(strict_types=1);

use App\Modules\Branch\Infrastructure\BranchServiceProvider;
use App\Modules\Settings\Infrastructure\SettingsServiceProvider;
use App\Modules\Shared\Infrastructure\SharedServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    SharedServiceProvider::class,
    SettingsServiceProvider::class,
    BranchServiceProvider::class,
];
