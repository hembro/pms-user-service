<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Actions\Users\UpdatePmsProfile;
use App\Commands\Users\UpdatePmsProfileCommand;
use App\Http\Requests\Api\V1\Users\UpdatePmsProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class UpdatePmsProfileController
{
    public function __construct(
        public readonly UpdatePmsProfile $action
    ) {}

    public function __invoke(UpdatePmsProfileRequest $request, User $user): JsonResponse
    {
        $user = $this->action->handle(
            command: UpdatePmsProfileCommand::fromRequest($request, $user),
        );

        return JsonResponse::success(['id' => $user->id], 'PMS profile updated successfully.');
    }
}
