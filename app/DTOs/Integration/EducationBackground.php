<?php

declare(strict_types=1);

namespace App\DTOs\Integration;

use App\Enums\EducationLevel;

final readonly class EducationBackground
{
    public function __construct(
        public EducationLevel $level,
        public ?string $school,
        public ?string $degree,
        public ?string $year,
        public ?string $awards = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            level: EducationLevel::tryFrom($data['level']) ?? EducationLevel::UNSPECIFIED,
            school: $data['school'] ?? null,
            degree: $data['degree'] ?? null,
            year: $data['year'] ?? null,
            awards: $data['awards'] ?? null,
        );
    }
}
