<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ClusterNamespace.
 *
 * This class is the model for cluster namespaces.
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
class ClusterNamespace extends Model
{
    use SoftDeletes;
    use HasUuids;

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
