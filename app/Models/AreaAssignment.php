<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read ?string $remarks
 * @property-read ?CarbonInterface $created_at
 * @property-read ?CarbonInterface $updated_at
 * @property-read ?Collection<User> $users
 */
final class AreaAssignment extends Model
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
            table: 'area_assignment_user',
            foreignPivotKey: 'area_assignment_id',
            relatedPivotKey: 'user_id'
        )->withTimestamps();
    }
}
