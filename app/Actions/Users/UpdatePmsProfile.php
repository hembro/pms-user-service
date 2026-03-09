<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Commands\Users\UpdatePmsProfileCommand;
use App\DTOs\Users\EducationBackground;
use App\Models\Designation;
use App\Models\Expertise;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;

final readonly class UpdatePmsProfile
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(UpdatePmsProfileCommand $command): User
    {
        $user = $this->db->transaction(
            callback: function () use ($command): User {

                $command->user->update([
                    'employment_status' => $command->employmentStatus,
                    'expertise_id' => $this->resolveCreatableId(Expertise::class, $command->expertiseInput),
                    'designation_id' => $this->resolveCreatableId(Designation::class, $command->designationInput),
                ]);

                $command->user->areaAssignments()->sync($command->areaAssignments);
                $command->user->divisions()->sync($command->divisions);

                if ($command->educationBackgrounds !== null) {

                    $command->user->educationBackgrounds()->delete();

                    $educationRecords = array_map(
                        fn (EducationBackground $bg): array => $bg->toAttributes(),
                        $command->educationBackgrounds
                    );

                    if (! empty($educationRecords)) {
                        $command->user->educationBackgrounds()->createMany($educationRecords);
                    }
                }

                return $command->user;
            }
        );

        return $user->refresh();
    }

    private function resolveCreatableId(string $modelClass, ?string $input): ?string
    {
        if (empty($input)) {
            return null;
        }

        if (Str::isUlid($input)) {
            return $modelClass::query()->where('id', $input)->exists() ? $input : null;
        }

        $normalized = preg_replace('/\s+/', ' ', mb_trim($input));

        $normalized = Str::title($normalized);

        return $modelClass::query()
            ->firstOrCreate(['name' => $normalized])
            ->getKey();
    }
}
