<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PodMetricContainer.
 *
 * This class is the model for kubernetes pod container metrics.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $pod_metric_id
 * @property string $name
 * @property string $cpu_usage
 * @property string $memory_usage
 * @property Carbon $metric_created_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class PodMetricContainer extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pod_metric_containers';

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
        'metric_created_at' => 'datetime',
    ];

    /**
     * Relation to pod metric.
     *
     * @return HasOne
     */
    public function podMetric(): HasOne
    {
        return $this->hasOne(PodMetric::class, 'id', 'pod_metric_id');
    }
}
