<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EducationLevel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EducationBackground extends Model
{
    use HasUlids;

    protected $guarded = [
        'id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: User::class,
            foreignKey: 'user_id',
            ownerKey: 'id',
        );
    }

    protected function casts()
    {
        return [
            'level' => EducationLevel::class,
        ];
    }
}
