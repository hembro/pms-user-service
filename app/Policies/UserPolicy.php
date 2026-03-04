<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    public function update(User $actor, User $target): bool
    {
        // PMS Admins bypass division checks
        if (in_array('pms.admin', $actor->roles)) {
            return true;
        }

        // Are they a Division Admin?
        if (! in_array('pms.user.division-admin', $actor->roles)) {
            return false;
        }

        // Do they share a division in the PMS database?
        $actorDivisionIds = $actor->divisions()->pluck('divisions.id')->toArray();
        $targetDivisionIds = $target->divisions()->pluck('divisions.id')->toArray();

        return count(array_intersect($actorDivisionIds, $targetDivisionIds)) > 0;
    }
}
