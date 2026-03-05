<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DivisionStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read ?string $acronym
 * @property-read DivisionStatus $status
 * @property-read ?string $description
 * @property-read ?CarbonInterface $created_at
 * @property-read ?CarbonInterface $updated_at
 * @property-read ?Collection<User> $users
 */
final class Division extends Model
{
    use HasFactory;
    use HasUlids;

    protected $guarded = [
        'id',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            related: User::class,
            table: 'division_user',
            foreignPivotKey: 'division_id',
            relatedPivotKey: 'user_id',
        );
    }

    protected function casts()
    {
        return [
            'status' => DivisionStatus::class,
        ];
    }
}
