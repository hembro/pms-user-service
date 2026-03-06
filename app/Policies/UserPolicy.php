<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class UserPolicy
{
    public function update(User $actor, User $target): bool
    {
        // Users can always update their own profile.
        if ($actor->id === $target->id) {
            return true;
        }

        // PMS Admin: They have the permission to manage everyone
        if (Gate::forUser($actor)->check(Permission::PMS_USER_MANAGE_ALL)) {
            return true;
        }

        // Division Admins can only update users within their shared divisions.
        if (Gate::forUser($actor)->check(Permission::PMS_USER_MANAGE_DIVISION)) {

            $actorDivisions = $actor->divisions->pluck('id')->toArray();
            $targetDivisions = $target->divisions->pluck('id')->toArray();

            // If the intersection is not empty, they share at least one division
            return ! empty(array_intersect($actorDivisions, $targetDivisions));
        }

        return false;
    }
}
