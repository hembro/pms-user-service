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
use jeremyaliparo\IntegrationSchemas\Events\Users\UserProfileUpdatedEvent;
use Psr\Log\LoggerInterface;

final readonly class SyncUpdatedUser
{
    use SyncsUserContext;

    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function handle(UserProfileUpdatedEvent $event): void
    {
        /** @var UserAttributes $attributes */
        $attributes = $event->target->attributes;
        $rawContext = $event->systemContext;
        $context = SystemContext::fromArray($rawContext);

        $user = User::query()->find($event->target->id);

        if (! $user) {
            $this->logger->warning('Received profile update for unknown user in PMS.', [
                'target_user_id' => $event->target->id,
            ]);

            return;
        }

        $this->db->transaction(
            callback: function () use ($user, $event, $attributes, $context, $rawContext) {

                $updatePayload = [
                    'email' => $attributes->email,
                    'display_name' => $attributes->displayName,
                    'first_name' => $attributes->firstName,
                    'last_name' => $attributes->lastName,
                    'status' => $attributes->status,
                    'last_synced_at' => $event->occurredAt,
                ];

                if (array_key_exists('employment_status', $rawContext)) {
                    $updatePayload['employment_status'] = $context->employmentStatus;
                }

                if (array_key_exists('expertise_id', $rawContext)) {
                    $updatePayload['expertise_id'] = $this->getValidId(Expertise::class, $context->expertiseId);
                }

                if (array_key_exists('designation_id', $rawContext)) {
                    $updatePayload['designation_id'] = $this->getValidId(Designation::class, $context->designationId);
                }

                $user->update($updatePayload);

                if (array_key_exists('division_ids', $rawContext)) {
                    $user->divisions()->sync(
                        $this->getValidIds(Division::class, $context->divisionIds)
                    );
                }

                if (array_key_exists('area_assignment_ids', $rawContext)) {
                    $user->areaAssignments()->sync(
                        $this->getValidIds(AreaAssignment::class, $context->areaAssignmentIds)
                    );
                }

                if (array_key_exists('education_backgrounds', $rawContext)) {
                    $this->syncEducationBackgrounds($user, $context->educationBackgrounds);
                }
            }
        );
    }
}
