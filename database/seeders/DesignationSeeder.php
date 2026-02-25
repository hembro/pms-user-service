<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $expertises = [
            'Science Research Specialist I',
            'Science Research Specialist II',
            'Senior Science Research Specialist',
            'Supervising Science Research Specialist',
            'Chief Science Research Specialist',
            'Project Technical Assistant I',
            'Project Technical Assistant II',
            'Project Technical Assistant III',
            'Project Technical Assistant IV',
            'Project Technical Assistant V',
            'Project Technical Assistant VI',
            'Project Technical Specialist I',
            'Project Technical Specialist II',
            'Project Technical Specialist III',
            'Project Technical Specialist IV',
        ];

        $payload = array_map(fn (string $name) => [
            'id' => (string) Str::ulid(),
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ], $expertises);

        Designation::query()->upsert(
            values: $payload,
            uniqueBy: ['name'],
            update: ['updated_at']
        );
    }
}
