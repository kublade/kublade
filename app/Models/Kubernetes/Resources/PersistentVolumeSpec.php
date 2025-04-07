<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PersistentVolumeSpec.
 *
 * This class is the model for kubernetes persistent volume specs.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $persistent_volume_id
 * @property string $capacity
 * @property string $driver
 * @property string $volume_handle
 * @property string $filesystem_type
 * @property string $claim_kind
 * @property string $claim_namespace
 * @property string $claim_name
 * @property string $claim_uuid
 * @property string $claim_api_version
 * @property int    $claim_resource_version
 * @property string $persistent_volume_reclaim_policy
 * @property string $volume_mode
 * @property string $phase
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class PersistentVolumeSpec extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'persistent_volume_specs';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to persistent volume.
     *
     * @return HasOne
     */
    public function persistentVolume(): HasOne
    {
        return $this->hasOne(PersistentVolume::class, 'id', 'persistent_volume_id');
    }
}
