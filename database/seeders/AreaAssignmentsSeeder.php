<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AreaAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class AreaAssignmentsSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            'Agriculture and Food',
            'Environment and Natural Resources',
            'Disaster Mitigation and Management',
            'Energy',
            'Health',
            'Manufacturing',
            'Electronics',
            'ICT',
            'Biotechnology',
            'Nanotechnology',
            'Genomics',
            'Technology Transfer',
            'S&T Promotion',
            'Human Resources',
            'Space Technology Application',
            'Others',
        ];

        $payload = array_map(fn (string $title) => [
            'id' => (string) Str::ulid(),
            'title' => $title,
            'created_at' => now(),
            'updated_at' => now(),
        ], $areas);

        AreaAssignment::query()->upsert(
            values: $payload,
            uniqueBy: ['title'],
            update: ['updated_at']
        );
    }
}
