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
 * Class Ns.
 *
 * This class is the model for namespaces.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $cluster_id
 * @property string $type
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Ns extends Model
{
    use SoftDeletes;
    use HasUuids;
    use LogsActivity;

    public const TYPE_UTILITY = 'utility';

    public const TYPE_INGRESS = 'ingress';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cluster_namespaces';

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
