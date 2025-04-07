<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PodSpec.
 *
 * This class is the model for kubernetes Pod specs.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string      $id
 * @property string      $pod_id
 * @property string      $restart_policy
 * @property int         $termination_grace_period_seconds
 * @property string      $dns_policy
 * @property string|null $service_account_name
 * @property string|null $service_account
 * @property string|null $node_name
 * @property string      $scheduler_name
 * @property bool        $enable_service_links
 * @property int         $priority
 * @property string      $preemption_policy
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon      $deleted_at
 */
class PodSpec extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pod_specs';

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
        'enable_service_links' => 'boolean',
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
