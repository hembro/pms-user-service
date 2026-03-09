<?php

declare(strict_types=1);

namespace App\Commands\Users;

use App\DTOs\Users\EducationBackground;
use App\Enums\EmploymentStatus;
use App\Http\Requests\Api\V1\Users\UpdatePmsProfileRequest;
use App\Models\User;

final readonly class UpdatePmsProfileCommand
{
    /**
     * @param  ?array<EducationBackground>  $educationBackgrounds
     */
    public function __construct(
        public User $user,
        public ?EmploymentStatus $employmentStatus,
        public ?string $expertiseInput,
        public ?string $designationInput,
        public array $divisions,
        public array $areaAssignments,
        public ?array $educationBackgrounds,
    ) {}

    public static function fromRequest(UpdatePmsProfileRequest $request, User $user): self
    {
        $data = $request->validated();

        return new self(
            user: $user,
            employmentStatus: $request->enum('employment_status', EmploymentStatus::class),
            expertiseInput: $data['expertise'] ?? null,
            designationInput: $data['designation'] ?? null,
            divisions: $data['divisions'],
            areaAssignments: $data['area_assignments'],
            educationBackgrounds: isset($data['education_backgrounds'])
                ? array_map(
                    fn (array $bg) => EducationBackground::fromArray($bg),
                    $data['education_backgrounds']
                )
                : null,
        );
    }
}
