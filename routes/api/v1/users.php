<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Users\UpdatePmsProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api'])->group(function (): void {
    Route::post('/{user}/profile', UpdatePmsProfileController::class)
        ->name('profile.update');
});
