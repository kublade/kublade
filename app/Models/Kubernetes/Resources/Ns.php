<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Ns.
 *
 * This class is the model for kubernetes namespaces.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $deployment_id
 * @property string $uuid
 * @property string $api_version
 * @property string $name
 * @property int    $resource_version
 * @property Carbon $namespace_created_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Ns extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'namespaces';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'namespace_created_at' => 'datetime',
    ];

    /**
     * Relation to cluster.
     *
     * @return HasOne
     */
    public function cluster(): HasOne
    {
        return $this->hasOne(Cluster::class, 'id', 'cluster_id');
    }

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
     * Relation to persistent volumes.
     *
     * @return HasMany
     */
    public function persistentVolumes(): HasMany
    {
        return $this->hasMany(PersistentVolume::class, 'namespace_id', 'id');
    }

    /**
     * Relation to pods.
     *
     * @return HasMany
     */
    public function pods(): HasMany
    {
        return $this->hasMany(Pod::class, 'namespace_id', 'id');
    }

    /**
     * Relation to services.
     *
     * @return HasMany
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'namespace_id', 'id');
    }

    /**
     * Relation to container advisory metrics.
     *
     * @return HasMany
     */
    public function containerAdvisoryMetrics(): HasMany
    {
        return $this->hasMany(ContainerAdvisoryMetric::class, 'namespace_id', 'id');
    }
}
