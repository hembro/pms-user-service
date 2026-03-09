<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

final class UserPolicy
{
    public function update(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return true;
        }

        if ($actor->hasGatewayRole(Role::PMS_ADMIN)) {
            return true;
        }

        if ($actor->hasGatewayRole(Role::PMS_DIVISION_ADMIN)) {
            return $actor->divisions()
                ->whereIn('divisions.id', $target->divisions()->select('divisions.id'))
                ->exists();
        }

        return false;
    }
}
