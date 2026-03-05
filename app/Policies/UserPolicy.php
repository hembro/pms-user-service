<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

final class UserPolicy
{
    public function update(User $actor, User $target): bool
    {
        // Safely retrieve the roles injected by the Gateway
        $roles = $actor->gateway_roles ?? [];

        // PMS Admins can update anyone in the system.
        if (in_array(Role::PMS_ADMIN, $roles, true)) {
            return true;
        }

        // Users can always update their own profile.
        if ($actor->id === $target->id) {
            return true;
        }

        // Division Admins can only update users within their shared divisions.
        if (in_array(Role::PMS_DIVISION_ADMIN, $roles, true)) {

            $actorDivisionIds = $actor->divisions
                ->pluck('divisions.id')
                ->toArray();

            $targetDivisionIds = $target->divisions
                ->pluck('divisions.id')
                ->toArray();

            return count(array_intersect($actorDivisionIds, $targetDivisionIds)) > 0;
        }

        return false;
    }
}
