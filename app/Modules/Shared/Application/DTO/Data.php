<?php

declare(strict_types=1);

namespace App\Modules\Shared\Application\DTO;

use Spatie\LaravelData\Data as SpatieData;

/**
 * Base immutable DTO for data crossing layer boundaries. Concrete DTOs extend
 * this and declare `public readonly` properties so payloads are never mutated
 * in place (architecture §5 / coding-style immutability). Never pass raw arrays
 * between layers — wrap them in a Data subclass.
 */
abstract class Data extends SpatieData {}
