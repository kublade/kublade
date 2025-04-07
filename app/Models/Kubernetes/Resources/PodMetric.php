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
 * Class PodMetric.
 *
 * This class is the model for kubernetes pod metrics.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $pod_id
 * @property string $api_version
 * @property string $name
 * @property int    $resource_version
 * @property Carbon $pod_created_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class PodMetric extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pod_metrics';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to pod.
     *
     * @return HasOne
     */
    public function pod(): HasOne
    {
        return $this->hasOne(Pod::class, 'id', 'pod_id');
    }

    /**
     * Relation to pod metric containers.
     *
     * @return HasMany
     */
    public function podMetricContainers(): HasMany
    {
        return $this->hasMany(PodMetricContainer::class, 'pod_metric_id', 'id');
    }
}
