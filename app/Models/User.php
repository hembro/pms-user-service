<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read string $id
 * @property-read string $email
 * @property-read ?CarbonInterface $created_at
 * @property-read ?CarbonInterface $updated_at
 * @property-read ?PmsUserProfile $pmsProfile
 * @property-read ?Expertise $expertise
 * @property-read ?Designation $designations
 * @property-read ?Collection<Division> $divisions
 * @property-read ?Collection<AreaAssignment> $areaAssignments
 */
final class User extends Model
{
    use HasFactory, HasUlids;

    protected $guarded = [];

    protected $hidden = [];

    public function profile(): HasOne
    {
        return $this->hasOne(
            related: PmsUserProfile::class,
            foreignKey: 'user_id',
            localKey: 'id',
        );
    }

    public function expertise(): BelongsTo
    {
        return $this->belongsTo(
            related: Expertise::class,
            foreignKey: 'expertise_id',
            ownerKey: 'id',
        );
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(
            related: Designation::class,
            foreignKey: 'designation_id',
            ownerKey: 'id',
        );
    }

    public function divisions(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Division::class,
            table: 'division_user',
            foreignPivotKey: 'user_id',
            relatedPivotKey: 'division_id',
        )->withTimestamps();
    }

    public function areaAssignments(): BelongsToMany
    {
        return $this->belongsToMany(
            related: AreaAssignment::class,
            table: 'area_assignment_user',
            foreignPivotKey: 'user_id',
            relatedPivotKey: 'area_assignment_id',
        )->withTimestamps();
    }

    protected function casts(): array
    {
        return [];
    }
}
