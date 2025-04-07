<?php

declare(strict_types=1);

namespace App\Models\Projects\Deployments;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DeploymentPhpmyadmin.
 *
 * This class is the model for deployment phpmyadmin links.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $deployment_id
 * @property string $phpmyadmin_deployment_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class DeploymentPhpmyadmin extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'deployment_phpmyadmins';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to deployment.
     *
     * @return HasOne
     */
    public function deployment(): HasOne
    {
        return $this->hasOne(Deployment::class, 'id', 'deployment_id');
    }

    /**
     * Relation to deployment.
     *
     * @return HasOne
     */
    public function phpmyadminDeployment(): HasOne
    {
        return $this->hasOne(Deployment::class, 'id', 'phpmyadmin_deployment_id');
    }
}
