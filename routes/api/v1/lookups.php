<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Lookups\GetUserOnboardingLookupsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'throttle:api'])->group(function (): void {
    Route::get('/user-onboarding', GetUserOnboardingLookupsController::class)
        ->name('user-onboarding');
});
