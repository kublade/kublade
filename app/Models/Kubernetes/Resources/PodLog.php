<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PodLog.
 *
 * This class is the model for kubernetes pod logs.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $pod_id
 * @property string $logs
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class PodLog extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pod_logs';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'logs',
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
}
