<?php

declare(strict_types=1);

namespace App\Actions\Integration\Users;

use App\Actions\Integration\Users\Concerns\SyncsUserContext;
use App\DTOs\Integration\SystemContext;
use App\Models\AreaAssignment;
use App\Models\Designation;
use App\Models\Division;
use App\Models\Expertise;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserRegisteredEvent;

final readonly class SyncRegisteredUser
{
    use SyncsUserContext;

    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(UserRegisteredEvent $event): void
    {
        /** @var UserAttributes $attributes */
        $attributes = $event->target->attributes;
        $context = SystemContext::fromArray($event->systemContext);

        $this->db->transaction(
            callback: function () use ($event, $attributes, $context) {

                $user = User::query()->updateOrCreate(
                    ['id' => $event->target->id],
                    [
                        'email' => $attributes->email,
                        'display_name' => $attributes->displayName,
                        'first_name' => $attributes->firstName,
                        'last_name' => $attributes->lastName,
                        'status' => $attributes->status->value,

                        'employment_status' => $context->employmentStatus,
                        'expertise_id' => $this->getValidId(Expertise::class, $context->expertiseId),
                        'designation_id' => $this->getValidId(Designation::class, $context->designationId),

                        'last_synced_at' => $event->occurredAt,
                    ]
                );

                $user->divisions()->sync(
                    $this->getValidIds(Division::class, $context->divisionIds)
                );

                $user->areaAssignments()->sync(
                    $this->getValidIds(AreaAssignment::class, $context->areaAssignmentIds)
                );

                $this->syncEducationBackgrounds($user, $context->educationBackgrounds);
            }
        );
    }
}
