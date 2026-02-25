<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmploymentStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read string $user_id
 * @property-read ?string $expertise
 * @property-read ?string $designation
 * @property-read EmploymentStatus $employment_status
 * @property-read ?string $dpmis_pm_id
 * @property-read ?CarbonInterface $submitted_to_dpmis_at
 * @property-read ?CarbonInterface $created_at
 * @property-read ?CarbonInterface $updated_at
 * @property-read ?User $user
 */
final class PmsUserProfile extends Model
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

    protected function casts(): array
    {
        return [
            'employment_status' => EmploymentStatus::class,
            'submitted_to_dpmis_at' => 'datetime',
        ];
    }
}
