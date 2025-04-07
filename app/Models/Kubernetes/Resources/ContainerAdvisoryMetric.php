<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ContainerAdvisoryMetric.
 *
 * This class is the model for kubernetes node metrics.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string      $id
 * @property string      $namespace_id
 * @property string      $node_id
 * @property string      $pod_id
 * @property string      $key
 * @property string|null $interface
 * @property string      $value
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon      $deleted_at
 */
class ContainerAdvisoryMetric extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'container_advisory_metrics';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
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
     * Relation to node.
     *
     * @return HasOne
     */
    public function node(): HasOne
    {
        return $this->hasOne(Node::class, 'id', 'node_id');
    }

    /**
     * Relation to pod.
     *
     * @return HasOne
     */
    public function pod(): HasOne
    {
        return $this->hasOne(Pod::class, 'id', 'pod_id');
    }
}
