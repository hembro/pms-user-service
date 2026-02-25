<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class User
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [];

    protected function casts(): array
    {
        return [];
    }
}
