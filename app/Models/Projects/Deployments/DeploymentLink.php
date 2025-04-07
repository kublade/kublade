<?php

declare(strict_types=1);

namespace App\Models\Projects\Deployments;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DeploymentLink.
 *
 * This class is the model for deployment ingress rules.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $source_deployment_id
 * @property string $target_deployment_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class DeploymentLink extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'deployment_links';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to source deployment.
     *
     * @return HasOne
     */
    public function source(): HasOne
    {
        return $this->hasOne(Deployment::class, 'id', 'source_deployment_id');
    }

    /**
     * Relation to target deployment.
     *
     * @return HasOne
     */
    public function target(): HasOne
    {
        return $this->hasOne(Deployment::class, 'id', 'target_deployment_id');
    }
}
