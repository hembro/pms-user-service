<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case PMS_ADMIN = 'pms.admin';
    case PMS_DIVISION_ADMIN = 'pms.user.division-admin';
}
