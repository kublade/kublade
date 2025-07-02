<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Clusters;

use App\Models\Projects\Projects\Project;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sagalbot\Encryptable\Encryptable;

/**
 * Class ClusterProvisionerConfig.
 *
 * This class is the model for cluster provisioner config.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $project_id
 * @property string $provisioner
 * @property string $config
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class ClusterProvisionerConfig extends Model
{
    use Encryptable;
    use SoftDeletes;
    use HasUuids;
    use LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cluster_provisioner_configs';

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
        'config',
    ];

    /**
     * Relation to cluster.
     *
     * @return HasOne
     */
    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'id', 'project_id');
    }

    /**
     * Get the value attribute.
     *
     * @return mixed
     */
    public function getValueAttribute(): mixed
    {
        if (! $this->config) {
            return null;
        }

        return json_decode($this->config, true);
    }
}
