<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Metrics;

use App\Models\Kubernetes\Resources\Node;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ClusterMetricNode.
 *
 * This class is the model for cluster node metrics.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string                            $id
 * @property string                            $cluster_metric_id
 * @property string                            $node_id
 * @property Carbon                            $created_at
 * @property Carbon                            $updated_at
 * @property Carbon                            $deleted_at
 * @property ClusterMetric|null                $clusterMetric
 * @property Node|null                         $node
 * @property ClusterMetricNodeCapacity|null    $capacity
 * @property ClusterMetricNodeUsage|null       $usage
 * @property ClusterMetricNodeUtilization|null $utilization
 */
class ClusterMetricNode extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cluster_metric_nodes';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to capacity.
     *
     * @return HasOne
     */
    public function capacity(): HasOne
    {
        return $this->hasOne(ClusterMetricNodeCapacity::class, 'cluster_metric_node_id', 'id');
    }

    /**
     * Relation to usage.
     *
     * @return HasOne
     */
    public function usage(): HasOne
    {
        return $this->hasOne(ClusterMetricNodeUsage::class, 'cluster_metric_node_id', 'id');
    }

    /**
     * Relation to utilization.
     *
     * @return HasOne
     */
    public function utilization(): HasOne
    {
        return $this->hasOne(ClusterMetricNodeUtilization::class, 'cluster_metric_node_id', 'id');
    }

    /**
     * Relation to cluster metric.
     *
     * @return HasOne
     */
    public function clusterMetric(): HasOne
    {
        return $this->hasOne(ClusterMetric::class, 'id', 'cluster_metric_id');
    }

    /**
     * Relation to node.
     *
     * @return HasOne
     */
    public function node(): HasOne
    {
        return $this->hasOne(Node::class, 'id', 'node_id')->withTrashed();
    }
}
