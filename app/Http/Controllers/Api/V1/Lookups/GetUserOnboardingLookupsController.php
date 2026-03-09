<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Lookups;

use App\Enums\EducationLevel;
use App\Enums\EmploymentStatus;
use App\Models\AreaAssignment;
use App\Models\Designation;
use App\Models\Division;
use App\Models\EducationBackground;
use App\Models\Expertise;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use jeremyaliparo\Foundation\Enums\System;

final class GetUserOnboardingLookupsController
{
    public function __invoke(): JsonResponse
    {
        $lookups = Cache::remember(
            key: 'lookups:user-onboarding',
            ttl: now()->addHours(24),
            callback: fn () => [
                'systems' => System::options(),
                'employment_statuses' => EmploymentStatus::options(),
                'education_levels' => EducationLevel::options(),

                'divisions' => Cache::rememberForever(
                    key: 'lookups:division',
                    callback: fn (): array => Division::query()
                        ->select(['id as value', 'name as label'])
                        ->get()
                        ->toArray()
                ),

                'area_assignments' => Cache::rememberForever(
                    key: 'lookups:area_assignment',
                    callback: fn (): array => AreaAssignment::query()
                        ->select(['id as value', 'name as label'])
                        ->get()
                        ->toArray()
                ),

                'designations' => Cache::rememberForever(
                    key: 'lookups:designation',
                    callback: fn (): array => Designation::query()
                        ->select(['id as value', 'name as label'])
                        ->get()
                        ->toArray()
                ),

                'expertises' => Cache::rememberForever(
                    key: 'lookups:expertise',
                    callback: fn (): array => Expertise::query()
                        ->select(['id as value', 'name as label'])
                        ->get()
                        ->toArray()
                ),

                'schools' => Cache::rememberForever(
                    key: 'lookups:school',
                    callback: fn (): array => EducationBackground::query()
                        ->select(['school as value', 'school as label'])
                        ->distinct()
                        ->get()
                        ->toArray()
                ),
            ],
        );

        return JsonResponse::success(
            data: $lookups,
            message: 'Onboarding lookups retrieved successfully.'
        );
    }
}
