<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('users')
        ->name('api.v1.users.')
        ->group(base_path('routes/api/v1/users.php'));
});
