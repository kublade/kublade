<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class NodeStatusDaemonEndpoint.
 *
 * This class is the model for kubernetes node status daemon endpoint.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $name
 * @property int    $port
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class NodeStatusDaemonEndpoint extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'node_status_daemon_endpoints';

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
