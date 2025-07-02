<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Clusters;

use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sagalbot\Encryptable\Encryptable;

/**
 * Class ClusterProvisionerMeta.
 *
 * This class is the model for cluster provisioner meta.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $cluster_id
 * @property string $key
 * @property string $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class ClusterProvisionerMeta extends Model
{
    use Encryptable;
    use SoftDeletes;
    use HasUuids;
    use LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cluster_provisioner_meta';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be encrypted.
     *
     * @var array<string>
     */
    protected $encryptable = [
        'value',
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
