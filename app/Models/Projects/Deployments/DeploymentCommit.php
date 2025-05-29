<?php

declare(strict_types=1);

namespace App\Models\Projects\Deployments;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Class DeploymentCommit.
 *
 * This class is the model for deployment commits.
 *
 * @OA\Schema(
 *     schema="DeploymentCommit",
 *     type="object",
 *
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="deployment_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="hash", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="message", type="string", example="Deployment commit message"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 * )
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $deployment_id
 * @property string $hash
 * @property string $message
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class DeploymentCommit extends Model
{
    use SoftDeletes;
    use HasUuids;
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'deployment_commits';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to deployment.
     *
     * @return HasOne
     */
    public function deployment(): HasOne
    {
        return $this->hasOne(Deployment::class, 'id', 'deployment_id');
    }

    /**
     * Relation to deployment commit data.
     *
     * @return HasMany
     */
    public function commitData(): HasMany
    {
        return $this->hasMany(DeploymentCommitData::class, 'deployment_commit_id', 'id');
    }

    /**
     * Relation to deployment commit secret data.
     *
     * @return HasMany
     */
    public function commitSecretData(): HasMany
    {
        return $this->hasMany(DeploymentCommitSecretData::class, 'deployment_commit_id', 'id');
    }

    /**
     * Get the diff attribute.
     *
     * @return Collection
     */
    public function getDiffAttribute(): Collection
    {
        // Load relationships with field
        $this->deployment->load(['deploymentData.field', 'deploymentSecretData.field']);

        return $this->commitData->map(function (DeploymentCommitData $item) {
            $current  = $this->deployment->deploymentData->where('key', $item->key)->first();
            $previous = $this->commitData->where('key', $item->key)->first()?->value;

            // Compare decrypted values
            if (decrypt($current?->value) === decrypt($previous)) {
                return null;
            }

            return [
                'type'     => 'plain',
                'label'    => $current?->field?->label,
                'current'  => decrypt($current?->value),
                'previous' => decrypt($previous),
                'key'      => $item->key,
            ];
        })->filter(function ($item) {
            return $item !== null;
        })->merge(
            $this->commitSecretData->map(function (DeploymentCommitSecretData $item) {
                $current  = $this->deployment->deploymentSecretData->where('key', $item->key)->first();
                $previous = $this->commitSecretData->where('key', $item->key)->first()?->value;

                // Compare decrypted values
                if (decrypt($current?->value) === decrypt($previous)) {
                    return null;
                }

                return [
                    'type'     => 'secret',
                    'label'    => $current?->field?->label,
                    'current'  => decrypt($current?->value),
                    'previous' => decrypt($previous),
                    'key'      => $item->key,
                ];
            })
        );
    }
}
