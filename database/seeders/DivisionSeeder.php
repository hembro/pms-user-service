<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DivisionStatus;
use App\Models\Division;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $divisions = [
            [
                'name' => 'Research Information, Communication, and Utilization Division',
                'acronym' => 'RICUD',
                'description' => null,
            ],
            [
                'name' => 'Research and Development Management Division',
                'acronym' => 'RDMD',
                'description' => null,
            ],
            [
                'name' => 'Institution Development Division',
                'acronym' => 'IDD',
                'description' => null,
            ],
            [
                'name' => 'Office of the Executive Director',
                'acronym' => 'OED',
                'description' => null,
            ],
            [
                'name' => 'Department of Health - PCHRD',
                'acronym' => 'DOH-PCHRD',
                'description' => null,
            ],
        ];

        $payload = array_map(fn (array $division) => [
            'id' => (string) Str::ulid(),
            'name' => $division['name'],
            'acronym' => $division['acronym'],
            'status' => DivisionStatus::ACTIVE,
            'created_at' => now(),
            'updated_at' => now(),
        ], $divisions);

        Division::query()->upsert(
            values: $payload,
            uniqueBy: ['name'],
            update: ['updated_at']
        );
    }
}
