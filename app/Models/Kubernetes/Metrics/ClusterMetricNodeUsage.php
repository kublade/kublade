<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Metrics;

use App\Models\Kubernetes\Node;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ClusterMetricNodeUsage.
 *
 * This class is the model for cluster node metric usages.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string                 $id
 * @property string                 $cluster_metric_node_id
 * @property float                  $cpu
 * @property float                  $memory
 * @property Carbon                 $created_at
 * @property Carbon                 $updated_at
 * @property Carbon                 $deleted_at
 * @property ClusterMetricNode|null $nodeMetric
 * @property Node|null              $node
 */
class ClusterMetricNodeUsage extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cluster_metric_node_usages';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to node metric.
     *
     * @return HasOne
     */
    public function nodeMetric(): HasOne
    {
        return $this->hasOne(ClusterMetricNode::class, 'id', 'cluster_metric_node_id');
    }
}
