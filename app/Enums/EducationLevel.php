<?php

declare(strict_types=1);

namespace App\Enums;

use jeremyaliparo\Foundation\Traits\HasEnumOptions;

enum EducationLevel: string
{
    use HasEnumOptions;

    case COLLEGE = 'college';
    case POST_BACCALAUREATE = 'post-baccalaureate';
    case MASTERS = 'masters';
    case DOCTORATE_PHD = 'doctorate-phd';
}
