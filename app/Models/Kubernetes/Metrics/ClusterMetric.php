<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Metrics;

use App\Models\Kubernetes\Clusters\Cluster;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ClusterMetric.
 *
 * This class is the model for cluster metric.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string                             $id
 * @property Carbon                             $created_at
 * @property Carbon                             $updated_at
 * @property Carbon                             $deleted_at
 * @property ClusterMetricCapacity|null         $capacity
 * @property ClusterMetricUsage|null            $usage
 * @property ClusterMetricUtilization|null      $utilization
 * @property Collection<ClusterMetricNode>|null $nodeMetrics
 */
class ClusterMetric extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cluster_metrics';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
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
     * Relation to capacity.
     *
     * @return HasOne
     */
    public function capacity(): HasOne
    {
        return $this->hasOne(ClusterMetricCapacity::class, 'cluster_metric_id', 'id');
    }

    /**
     * Relation to usage.
     *
     * @return HasOne
     */
    public function usage(): HasOne
    {
        return $this->hasOne(ClusterMetricUsage::class, 'cluster_metric_id', 'id');
    }

    /**
     * Relation to utilization.
     *
     * @return HasOne
     */
    public function utilization(): HasOne
    {
        return $this->hasOne(ClusterMetricUtilization::class, 'cluster_metric_id', 'id');
    }

    /**
     * Relation to node metric.
     *
     * @return HasMany
     */
    public function nodeMetrics(): HasMany
    {
        return $this->hasMany(ClusterMetricNode::class, 'cluster_metric_id', 'id');
    }
}
