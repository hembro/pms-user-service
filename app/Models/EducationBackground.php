<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EducationLevel;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * @property-read string $id
 * @property-read string $user_id
 * @property-read EducationLevel $level
 * @property-read string $school
 * @property-read string $degree
 * @property-read string $year
 * @property-read ?string $awards
 * @property-read ?CarbonInterface $created_at
 * @property-read ?CarbonInterface $updated_at
 */
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

    protected static function booted(): void
    {
        self::saved(fn () => Cache::forget('lookups:school'));
        self::deleted(fn () => Cache::forget('lookups:school'));
    }

    protected function casts(): array
    {
        return [
            'level' => EducationLevel::class,
        ];
    }
}
