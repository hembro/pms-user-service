<?php

declare(strict_types=1);

namespace App\DTOs\Users;

use App\Enums\EducationLevel;

final readonly class EducationBackground
{
    public function __construct(
        public EducationLevel $level,
        public string $school,
        public string $degree,
        public string $year,
        public ?string $awards,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            level: EducationLevel::from($data['level']),
            school: $data['school'],
            degree: $data['degree'],
            year: $data['year'],
            awards: $data['awards'] ?? null,
        );
    }

    public function toAttributes(): array
    {
        return [
            'level' => $this->level,
            'school' => $this->school,
            'degree' => $this->degree,
            'year' => $this->year,
            'awards' => $this->awards,
        ];
    }
}
