<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::prefix('users')
        ->name('users.')
        ->group(base_path('routes/api/v1/users.php'));

    Route::prefix('lookups')
        ->name('lookups.')
        ->group(base_path('routes/api/v1/lookups.php'));
});
