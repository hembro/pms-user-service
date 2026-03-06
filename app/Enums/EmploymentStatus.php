<?php

declare(strict_types=1);

namespace App\Enums;

enum EmploymentStatus: string
{
    case PERMANENT_PERSONNEL = 'permanent-personnel';
    case CONTRACT_OF_SERVICE_PERSONNEL = 'contract-of-service-personnel';
    case OUTSOURCED_PERSONNEL = 'outsourced-personnel';
    case JOB_ORDER_PERSONNEL = 'job-order-personnel';
}
