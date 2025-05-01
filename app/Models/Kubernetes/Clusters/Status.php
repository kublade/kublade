<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Clusters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Resource.
 *
 * This class is the model for cluster resources.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $cluster_id
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Status extends Model
{
    use SoftDeletes;
    use HasUuids;

    public const STATUS_ONLINE = 'online';

    public const STATUS_OFFLINE = 'offline';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cluster_status';

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
}
