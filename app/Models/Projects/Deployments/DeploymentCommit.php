<?php

declare(strict_types=1);

namespace App\Models\Projects\Deployments;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DeploymentCommit.
 *
 * This class is the model for deployment commits.
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
}
