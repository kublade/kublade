<?php

declare(strict_types=1);

namespace App\Models\Projects\Deployments;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sagalbot\Encryptable\Encryptable;

/**
 * Class DeploymentCommitSecretData.
 *
 * This class is the model for deployment commit data key value pairs.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $deployment_commit_id
 * @property string $key
 * @property string $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class DeploymentCommitSecretData extends Model
{
    use SoftDeletes;
    use HasUuids;
    use Encryptable;
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'deployment_commit_secret_data';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be encrypted.
     *
     * @var array<string>
     */
    protected $encryptable = [
        'value',
    ];

    /**
     * Relation to commit.
     *
     * @return HasOne
     */
    public function commit(): HasOne
    {
        return $this->hasOne(DeploymentCommit::class, 'id', 'deployment_commit_id');
    }
}
