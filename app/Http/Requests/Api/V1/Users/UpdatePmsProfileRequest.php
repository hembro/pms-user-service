<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Users;

use App\Enums\EducationLevel;
use App\Enums\EmploymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePmsProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'employment_status' => ['nullable', 'string', Rule::enum(EmploymentStatus::class)],

            'expertise' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],

            'divisions' => ['required', 'array'],
            'divisions.*' => ['required', 'string', 'max:255', 'exists:divisions,id'],

            'area_assignments' => ['required', 'array'],
            'area_assignments.*' => ['required', 'string', 'max:255', 'exists:area_assignments,id'],

            'education_backgrounds' => ['nullable', 'array'],
            'education_backgrounds.*.level' => ['required', 'string', Rule::enum(EducationLevel::class)],
            'education_backgrounds.*.school' => ['required', 'string', 'max:255'],
            'education_backgrounds.*.degree' => ['required', 'string', 'max:255'],
            'education_backgrounds.*.year' => ['required', 'string', 'max:50'],
            'education_backgrounds.*.awards' => ['nullable', 'string'],
        ];
    }
}
