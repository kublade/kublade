<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PodVolume.
 *
 * This class is the model for linking kubernetes pods with persistent volumes.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $pod_id
 * @property string $persistent_volume_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class PodVolume extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pod_volumes';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to pod.
     *
     * @return HasOne
     */
    public function pod(): HasOne
    {
        return $this->hasOne(Pod::class, 'id', 'pod_id');
    }

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
