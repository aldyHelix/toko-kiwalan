<?php

declare(strict_types=1);

namespace App\Modules\Branch\Presentation\Filament;

use App\Modules\Branch\Presentation\Filament\Resources\BranchResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Registers the Branch module's Filament resources with the admin panel — the
 * same per-module plugin convention used by the Settings module's SettingsPlugin.
 */
final class BranchPlugin implements Plugin
{
    public static function make(): self
    {
        return new self;
    }

    public function getId(): string
    {
        return 'branch';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            BranchResource::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}
