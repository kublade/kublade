<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Clusters;

use App\Models\Kubernetes\Resources\Node;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Projects\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Cluster.
 *
 * This class is the model for clusters.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $user_id
 * @property string $project_id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Cluster extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'clusters';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to user.
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Relation to project.
     *
     * @return HasOne
     */
    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'id', 'project_id');
    }

    /**
     * Relation to k8s credentials.
     *
     * @return HasOne
     */
    public function k8sCredentials(): HasOne
    {
        return $this->hasOne(K8sCredential::class, 'cluster_id', 'id');
    }

    /**
     * Relation to git credentials.
     *
     * @return HasOne
     */
    public function gitCredentials(): HasOne
    {
        return $this->hasOne(GitCredential::class, 'cluster_id', 'id');
    }

    /**
     * Relation to resources.
     *
     * @return HasMany
     */
    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class, 'cluster_id', 'id');
    }

    /**
     * Relation to status.
     *
     * @return HasMany
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class, 'cluster_id', 'id');
    }

    /**
     * Relation to namespaces.
     *
     * @return HasMany
     */
    public function namespaces(): HasMany
    {
        return $this->hasMany(Ns::class, 'cluster_id', 'id');
    }

    /**
     * Relation to nodes.
     *
     * @return HasMany
     */
    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class, 'cluster_id', 'id');
    }

    /**
     * Relation to deployments.
     *
     * @return HasMany
     */
    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class, 'cluster_id', 'id');
    }

    /**
     * Get the utility namespace.
     *
     * @return Ns|null
     */
    public function getUtilityNamespaceAttribute(): ?Ns
    {
        return $this->namespaces()->where('type', '=', Ns::TYPE_UTILITY)->first();
    }

    /**
     * Get the ingress namespace.
     *
     * @return Ns|null
     */
    public function getIngressNamespaceAttribute(): ?Ns
    {
        return $this->namespaces()->where('type', '=', Ns::TYPE_INGRESS)->first();
    }

    /**
     * Get the ingress namespace.
     *
     * @return Ns|null
     */
    public function getLimitAttribute(): ?Resource
    {
        return $this->resources()->where('type', '=', Resource::TYPE_LIMIT)->first();
    }

    /**
     * Get the alert resource.
     *
     * @return resource|null
     */
    public function getAlertAttribute(): ?Resource
    {
        return $this->resources()->where('type', '=', Resource::TYPE_ALERT)->first();
    }

    /**
     * Get the repository path attribute.
     *
     * @return string
     */
    public function getRepositoryPathAttribute(): string
    {
        return 'flux-repository/' . $this->id;
    }

    /**
     * Get the repository deployment path attribute.
     *
     * @return string
     */
    public function getRepositoryDeploymentPathAttribute(): string
    {
        return 'flux-repository/' . $this->id . $this->gitCredentials->base_path;
    }

    /**
     * Get the status attribute.
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        return $this->statuses()->orderByDesc('created_at')->first()?->status ?? Status::STATUS_OFFLINE;
    }
}
