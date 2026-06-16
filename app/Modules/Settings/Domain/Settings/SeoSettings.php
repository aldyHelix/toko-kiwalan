<?php

declare(strict_types=1);

namespace App\Modules\Settings\Domain\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Global SEO defaults. Per-entity meta (Fase 7) falls back to these, which in
 * turn fall back to product defaults — see SeoResolver in the Seo module.
 */
class SeoSettings extends Settings
{
    public string $meta_title;

    public string $meta_description;

    public ?string $og_image;

    /** Default robots directive, e.g. "index,follow". */
    public string $robots;

    public static function group(): string
    {
        return 'seo';
    }
}
