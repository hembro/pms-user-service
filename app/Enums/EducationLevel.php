<?php

declare(strict_types=1);

namespace App\Enums;

enum EducationLevel: string
{
    case COLLEGE = 'college';
    case POST_BACCALAUREATE = 'post-baccalaureate';
    case MASTERS = 'masters';
    case DOCTORATE_PHD = 'doctorate-phd';
    case UNSPECIFIED = 'unspecified';
}
