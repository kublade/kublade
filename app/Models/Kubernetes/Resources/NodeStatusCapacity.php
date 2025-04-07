<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class NodeStatusCapacity.
 *
 * This class is the model for kubernetes node status capacity.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $node_id
 * @property int    $cpu
 * @property string $ephemeral_storage
 * @property int    $hugepages_1gi
 * @property int    $hugepages_2mi
 * @property string $memory
 * @property int    $pods
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class NodeStatusCapacity extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'node_status_capacities';

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
