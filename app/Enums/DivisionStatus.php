<?php

declare(strict_types=1);

namespace App\Enums;

use jeremyaliparo\Foundation\Traits\HasEnumOptions;

enum DivisionStatus: string
{
    use HasEnumOptions;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
