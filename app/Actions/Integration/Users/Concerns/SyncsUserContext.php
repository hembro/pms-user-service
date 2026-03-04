<?php

declare(strict_types=1);

namespace App\Actions\Integration\Users\Concerns;

use App\DTOs\Integration\EducationBackground;
use App\Models\User;

trait SyncsUserContext
{
    /**
     * Ensures the provided ID actually exists in the local database table.
     */
    protected function getValidId(string $modelClass, ?string $id): ?string
    {
        if (empty($id) || ! is_string($id)) {
            return null;
        }

        return $modelClass::query()->where('id', $id)->exists() ? $id : null;
    }

    /**
     * Filters an array of IDs, returning only those that exist in the local database.
     */
    protected function getValidIds(string $modelClass, mixed $ids): array
    {
        if (empty($ids) || ! is_array($ids)) {
            return [];
        }

        return $modelClass::query()->whereIn('id', $ids)->pluck('id')->toArray();
    }

    /**
     * Idempotently syncs education backgrounds.
     */
    protected function syncEducationBackgrounds(User $user, mixed $backgrounds): void
    {
        if (empty($backgrounds) || ! is_array($backgrounds)) {
            return;
        }

        // Clear existing records for a fresh sync
        $user->educationBackgrounds()->delete();

        $educationRecords = array_map(
            callback: fn (EducationBackground $education): array => [
                'level' => $education->level,
                'school' => $education->school,
                'degree' => $education->degree,
                'year' => $education->year,
                'awards' => $education->awards,
            ],
            array: $backgrounds
        );

        $user->educationBackgrounds()->createMany($educationRecords);
    }
}
