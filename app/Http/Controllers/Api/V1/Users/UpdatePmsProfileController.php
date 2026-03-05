<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Actions\Users\UpdatePmsProfile;
use App\Commands\Users\UpdatePmsProfileCommand;
use App\Http\Requests\Api\V1\Users\UpdatePmsProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use jeremyaliparo\HttpResponses\Traits\HasApiResponse;

final class UpdatePmsProfileController
{
    use HasApiResponse;

    public function __construct(
        public readonly UpdatePmsProfile $action
    ) {}

    public function __invoke(UpdatePmsProfileRequest $request, User $user): JsonResponse
    {
        Gate::authorize('update', $user);

        $user = $this->action->handle(
            command: UpdatePmsProfileCommand::fromRequest($request),
            user: $user
        );

        return $this->success(['id' => $user->id], 'PMS profile updated successfully.');
    }
}
