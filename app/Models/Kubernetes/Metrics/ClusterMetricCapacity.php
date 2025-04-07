<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Metrics;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ClusterMetricCapacity.
 *
 * This class is the model for cluster metric capacities.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string             $id
 * @property string             $cluster_metric_id
 * @property float              $cpu
 * @property float              $storage
 * @property float              $memory
 * @property float              $pods
 * @property Carbon             $created_at
 * @property Carbon             $updated_at
 * @property Carbon             $deleted_at
 * @property ClusterMetric|null $metric
 */
class ClusterMetricCapacity extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cluster_metric_capacities';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to metric.
     *
     * @return HasOne
     */
    public function metric(): HasOne
    {
        return $this->hasOne(ClusterMetric::class, 'id', 'cluster_metric_id');
    }
}
