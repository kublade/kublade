<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Pod.
 *
 * This class is the model for kubernetes pods.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $namespace_id
 * @property string $api_version
 * @property string $name
 * @property string $resource_version
 * @property Carbon $pod_created_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Pod extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pods';

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
        'pod_created_at' => 'datetime',
    ];

    /**
     * Relation to namespace.
     *
     * @return HasOne
     */
    public function namespace(): HasOne
    {
        return $this->hasOne(Ns::class, 'id', 'namespace_id');
    }

    /**
     * Relation to pod metrics.
     *
     * @return HasMany
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(PodMetric::class, 'pod_id', 'id');
    }

    /**
     * Relation to pod logs.
     *
     * @return HasOne
     */
    public function logs(): HasOne
    {
        return $this->hasOne(PodLog::class, 'pod_id', 'id');
    }

    /**
     * Relation to specs.
     *
     * @return HasMany
     */
    public function specs(): HasMany
    {
        return $this->hasMany(PodSpec::class, 'pod_id', 'id');
    }

    /**
     * Relation to volume links.
     *
     * @return HasMany
     */
    public function volumes(): HasMany
    {
        return $this->hasMany(PodVolume::class, 'pod_id', 'id');
    }

    /**
     * Relation to container advisory metrics.
     *
     * @return HasMany
     */
    public function containerAdvisoryMetrics(): HasMany
    {
        return $this->hasMany(ContainerAdvisoryMetric::class, 'pod_id', 'id');
    }
}
