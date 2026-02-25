<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read ?Collection<User> $users
 */
final class Expertise extends Model
{
    use HasUlids;

    protected $guarded = [
        'id',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(
            related: User::class,
            foreignKey: 'expertise_id',
            localKey: 'id',
        );
    }
}
