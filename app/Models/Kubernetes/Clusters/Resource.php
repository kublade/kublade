<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Clusters;

use App\Traits\LogsActivity;
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
 * @property string $type
 * @property float  $cpu
 * @property float  $memory
 * @property float  $storage
 * @property float  $pods
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Resource extends Model
{
    use SoftDeletes;
    use HasUuids;
    use LogsActivity;

    public const TYPE_LIMIT = 'limit';

    public const TYPE_ALERT = 'alert';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cluster_resources';

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
