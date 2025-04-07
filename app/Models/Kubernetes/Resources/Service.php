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
 * Class Service.
 *
 * This class is the model for kubernetes services.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $namespace_id
 * @property string $uuid
 * @property string $name
 * @property string $public_ip
 * @property string $resource_version
 * @property Carbon $service_created_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Service extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'services';

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
        'service_created_at' => 'datetime',
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
     * Relation to service ports.
     *
     * @return HasMany
     */
    public function ports(): HasMany
    {
        return $this->hasMany(ServicePort::class, 'service_id', 'id');
    }
}
