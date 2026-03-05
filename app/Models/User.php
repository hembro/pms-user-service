<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmploymentStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;

/**
 * @property-read string $id
 * @property-read string $email
 * @property-read string $name
 * @property-read UserStatus $status
 * @property-read ?string $avatar_url
 * @property-read Collection<Division> $divisions
 * @property-read ?Expertise $expertise
 * @property-read ?Designation $designation
 * @property-read EmploymentStatus $employment_status
 * @property-read Collection<AreaAssignment> $areaAssignments
 * @property-read ?Collection<EducationBackground> $educationBackgrounds
 * @property-read ?CarbonInterface $last_synced_at
 * @property-read ?CarbonInterface $created_at
 * @property-read ?CarbonInterface $updated_at
 */
final class User extends Model
{
    use HasUlids;

    protected $guarded = [];

    protected $hidden = [];

    public function divisions(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Division::class,
            table: 'division_user',
            foreignPivotKey: 'user_id',
            relatedPivotKey: 'division_id',
        )->withTimestamps();
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

    public function areaAssignments(): BelongsToMany
    {
        return $this->belongsToMany(
            related: AreaAssignment::class,
            table: 'area_assignment_user',
            foreignPivotKey: 'user_id',
            relatedPivotKey: 'area_assignment_id',
        )->withTimestamps();
    }

    public function educationBackgrounds(): HasMany
    {
        return $this->hasMany(
            related: EducationBackground::class,
            foreignKey: 'user_id',
            localKey: 'id',
        );
    }

    protected function casts(): array
    {
        return [
            'status' => UserStatus::class,
            'employment_status' => EmploymentStatus::class,
            'last_synced_at' => 'datetime',
        ];
    }
}
