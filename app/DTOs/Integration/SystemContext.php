<?php

declare(strict_types=1);

namespace App\DTOs\Integration;

use App\Enums\EmploymentStatus;

final readonly class SystemContext
{
    public function __construct(
        public EmploymentStatus $employmentStatus,
        public ?string $expertiseId,
        public ?string $designationId,
        public ?array $divisionIds,
        public ?array $areaAssignmentIds,
        public ?array $educationBackgrounds,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            employmentStatus: EmploymentStatus::tryFrom($data['employment_status'] ?? '') ?? EmploymentStatus::UNSPECIFIED,
            expertiseId: $data['expertise_id'] ?? null,
            designationId: $data['designation_id'] ?? null,
            divisionIds: $data['division_ids'] ?? null,
            areaAssignmentIds: $data['area_assignment_ids'] ?? null,
            educationBackgrounds: isset($data['education_backgrounds']) && is_array($data['education_backgrounds'])
                ? array_map(fn (array $bg) => EducationBackground::fromArray($bg), $data['education_backgrounds'])
                : null,
        );
    }
}
