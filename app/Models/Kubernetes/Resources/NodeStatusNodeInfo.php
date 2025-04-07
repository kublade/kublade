<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class NodeStatusNodeInfo.
 *
 * This class is the model for kubernetes node status node info.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $node_id
 * @property string $machine_id
 * @property string $system_uuid
 * @property string $boot_id
 * @property string $kernel_version
 * @property string $os_image
 * @property string $container_runtime_version
 * @property string $kubelet_version
 * @property string $kube_proxy_version
 * @property string $operating_system
 * @property string $architecture
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class NodeStatusNodeInfo extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'node_status_node_infos';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to node.
     *
     * @return HasOne
     */
    public function node(): HasOne
    {
        return $this->hasOne(Node::class, 'id', 'node_id');
    }
}
