<?php

declare(strict_types=1);

namespace App\Enums;

enum DivisionStatus: string
{
    case ENABLED = 'enabled';
    case DISABLED = 'disabled';
}
