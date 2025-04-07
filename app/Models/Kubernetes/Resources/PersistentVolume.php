<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PersistentVolume.
 *
 * This class is the model for kubernetes persistent volumes.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int    $id
 * @property int    $namespace_id
 * @property string $name
 * @property string $uuid
 * @property string $resource_version
 * @property Carbon $volume_created_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class PersistentVolume extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'persistent_volumes';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'volume_created_at' => 'datetime',
    ];

    /**
     * Relation to namespace.
     *
     * @return HasOne
     */
    public function namespace(): HasOne
    {
        return $this->hasOne(Ns::class, 'id', 'namespace_id');
    }

    /**
     * Relation to volume links.
     *
     * @return HasMany
     */
    public function volumes(): HasMany
    {
        return $this->hasMany(PodVolume::class, 'persistent_volume_id', 'id');
    }

    /**
     * Relation to volume links.
     *
     * @return HasMany
     */
    public function specs(): HasMany
    {
        return $this->hasMany(PersistentVolumeSpec::class, 'persistent_volume_id', 'id');
    }
}
