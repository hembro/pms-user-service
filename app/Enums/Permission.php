<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    case PMS_USER_MANAGE_ALL = 'pms.user.manage-all';
    case PMS_USER_MANAGE_DIVISION = 'pms.user.manage-division';
}
