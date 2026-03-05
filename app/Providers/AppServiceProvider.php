<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
}
