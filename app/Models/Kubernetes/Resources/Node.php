<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use App\Models\Kubernetes\Clusters\Cluster;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

use function str_contains;

/**
 * Class Node.
 *
 * This class is the model for kubernetes nodes.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $uuid
 * @property string $api_version
 * @property string $name
 * @property int    $resource_version
 * @property Carbon $node_created_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Node extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nodes';

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
        'node_created_at' => 'datetime',
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
     * Relation to metrics.
     *
     * @return HasMany
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(NodeMetric::class, 'node_id', 'id');
    }

    /**
     * Relation to specs.
     *
     * @return HasMany
     */
    public function specs(): HasMany
    {
        return $this->hasMany(NodeSpec::class, 'node_id', 'id');
    }

    /**
     * Relation to addresses.
     *
     * @return HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(NodeStatusAddress::class, 'node_id', 'id');
    }

    /**
     * Relation to allocatables.
     *
     * @return HasMany
     */
    public function allocatables(): HasMany
    {
        return $this->hasMany(NodeStatusAllocatable::class, 'node_id', 'id');
    }

    /**
     * Relation to capacities.
     *
     * @return HasMany
     */
    public function capacities(): HasMany
    {
        return $this->hasMany(NodeStatusCapacity::class, 'node_id', 'id');
    }

    /**
     * Relation to conditions.
     *
     * @return HasMany
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(NodeStatusCondition::class, 'node_id', 'id');
    }

    /**
     * Relation to daemon endpoints.
     *
     * @return HasMany
     */
    public function daemonEndpoints(): HasMany
    {
        return $this->hasMany(NodeStatusDaemonEndpoint::class, 'node_id', 'id');
    }

    /**
     * Relation to images.
     *
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(NodeStatusImage::class, 'node_id', 'id');
    }

    /**
     * Relation to node info.
     *
     * @return HasMany
     */
    public function nodeInfos(): HasMany
    {
        return $this->hasMany(NodeStatusNodeInfo::class, 'node_id', 'id');
    }

    /**
     * Relation to volume attachments.
     *
     * @return HasMany
     */
    public function volumeAttachments(): HasMany
    {
        return $this->hasMany(NodeStatusVolumeAttachment::class, 'node_id', 'id');
    }

    /**
     * Relation to volume attachments.
     *
     * @return HasMany
     */
    public function volumeUses(): HasMany
    {
        return $this->hasMany(NodeStatusVolumeUse::class, 'node_id', 'id');
    }

    /**
     * Select a random node to deploy application onto.
     *
     * @throws Exception
     *
     * @return Node
     */
    public static function random()
    {
        // TODO: Select node by fewest utilization.

        return self::all()
            ->filter(function (Node $node) {
                return str_contains($node->name, 'agent');
            })
            ->random();
    }
}
