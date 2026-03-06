<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureGateway();
        $this->configureRateLimiting();
        $this->configureLocalPermissions();
    }

    private function configureGateway(): void
    {
        Auth::viaRequest('gateway', function (Request $request): ?User {

            $actorId = $request->header('X-User-Id');
            $rolesHeader = $request->header('X-User-Roles', '');

            if ($actorId && $actor = User::query()->with('divisions:id')->find($actorId)) {

                $rolesArray = array_filter(
                    array_map('trim', explode(',', $rolesHeader))
                );

                $actor->setAttribute('gateway_roles', $rolesArray);

                return $actor;
            }

            return null;
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?? $request->ip());
        });
    }

    private function configureLocalPermissions(): void
    {
        Gate::define(Permission::PMS_USER_MANAGE_ALL, function (User $actor): bool {
            return in_array(
                needle: Role::PMS_ADMIN->value,
                haystack: $actor->getAttribute('gateway_roles') ?? [],
                strict: true
            );
        });

        Gate::define(Permission::PMS_USER_MANAGE_DIVISION, function (User $actor): bool {
            return in_array(
                needle: Role::PMS_DIVISION_ADMIN->value,
                haystack: $actor->getAttribute('gateway_roles') ?? [],
                strict: true
            );
        });
    }
}
