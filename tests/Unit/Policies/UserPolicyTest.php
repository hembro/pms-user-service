<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\User;
use App\Policies\UserPolicy;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->policy = new UserPolicy();
});

describe('User Policy: Update PMS Profile', function () {

    it('allows a user to update their own profile', function () {

        $user = new User();
        $user->id = 'user-123';

        // Act
        $result = $this->policy->update($user, $user);

        // Assert
        expect($result)->toBeTrue();
    });

    it('denies a regular user from updating someone else\'s profile', function () {
        // Arrange
        $actor = new User();
        $actor->id = 'user-123';
        $actor->setAttribute('gateway_roles', ['pms.proponent']);

        $target = new User();
        $target->id = 'user-999';

        // Act
        $result = $this->policy->update($actor, $target);

        // Assert
        expect($result)->toBeFalse();
    });

    it('allows an admin to update someone else\'s profile', function () {
        // Arrange
        $admin = new User();
        $admin->id = 'admin-123';
        $admin->setAttribute('gateway_roles', [Role::PMS_ADMIN->value]);

        $target = new User();
        $target->id = 'user-999';

        // Act
        $result = $this->policy->update($admin, $target);

        // Assert
        expect($result)->toBeTrue();
    });
});
