<?php

declare(strict_types=1);

namespace App\Models\Projects\Deployments;

use App\Models\Kubernetes\Resources\Ns;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Deployment.
 *
 * This class is the model for deployments.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $user_id
 * @property string $template
 * @property string $uuid
 * @property bool   $paused
 * @property bool   $update
 * @property bool   $delete
 * @property Carbon $deployed_at
 * @property Carbon $deployment_updated_at
 * @property Carbon $creation_dispatched_at
 * @property Carbon $update_dispatched_at
 * @property Carbon $deletion_dispatched_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Deployment extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'deployments';

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
        'paused'                 => 'boolean',
        'update'                 => 'boolean',
        'delete'                 => 'boolean',
        'deployed_at'            => 'datetime',
        'deployment_updated_at'  => 'datetime',
        'creation_dispatched_at' => 'datetime',
        'update_dispatched_at'   => 'datetime',
        'deletion_dispatched_at' => 'datetime',
    ];

    /**
     * Relation to deployment metrics.
     *
     * @return HasMany
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(DeploymentMetric::class, 'deployment_id', 'id');
    }

    /**
     * Relation to deployment data.
     *
     * @return HasMany
     */
    public function deploymentData(): HasMany
    {
        return $this->hasMany(DeploymentData::class, 'deployment_id', 'id');
    }

    /**
     * Relation to deployment secret data.
     *
     * @return HasMany
     */
    public function deploymentSecretData(): HasMany
    {
        return $this->hasMany(DeploymentSecretData::class, 'deployment_id', 'id');
    }

    /**
     * Relation to deployment namespaces.
     *
     * @return HasMany
     */
    public function namespaces(): HasMany
    {
        return $this->hasMany(Ns::class, 'deployment_id', 'id');
    }

    /**
     * Relation to deployment.
     *
     * @return HasOne
     */
    public function limit(): HasOne
    {
        return $this->hasOne(DeploymentLimit::class, 'deployment_id', 'id');
    }

    /**
     * Relation to ftp deployment link.
     *
     * @return HasMany
     */
    public function ftpDeploymentLinks(): HasMany
    {
        return $this->hasMany(DeploymentFtp::class, 'deployment_id', 'id');
    }

    /**
     * Relation to deployment links.
     *
     * @return HasMany
     */
    public function deploymentFtpLinks(): HasMany
    {
        return $this->hasMany(DeploymentFtp::class, 'ftp_deployment_id', 'id');
    }

    /**
     * Relation to phpmyadmin deployment link.
     *
     * @return HasMany
     */
    public function phpmyadminDeploymentLinks(): HasMany
    {
        return $this->hasMany(DeploymentPhpmyadmin::class, 'deployment_id', 'id');
    }

    /**
     * Relation to deployment links.
     *
     * @return HasMany
     */
    public function deploymentPhpmyadminLinks(): HasMany
    {
        return $this->hasMany(DeploymentPhpmyadmin::class, 'phpmyadmin_deployment_id', 'id');
    }

    /**
     * Relation to reserved ports.
     *
     * @return HasMany
     */
    public function ports(): HasMany
    {
        return $this->hasMany(ReservedPort::class, 'deployment_id', 'id');
    }

    /**
     * Relation to ingress rules as source.
     *
     * @return HasMany
     */
    public function ingressAsSource(): HasMany
    {
        return $this->hasMany(DeploymentLink::class, 'source_deployment_id', 'id');
    }

    /**
     * Relation to ingress rules as source.
     *
     * @return HasMany
     */
    public function ingressAsTarget(): HasMany
    {
        return $this->hasMany(DeploymentLink::class, 'target_deployment_id', 'id');
    }

    /**
     * Relation to commits.
     *
     * @return HasMany
     */
    public function commits(): HasMany
    {
        return $this->hasMany(DeploymentCommit::class, 'deployment_id', 'id');
    }
}
