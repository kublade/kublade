<?php

declare(strict_types=1);

namespace App\Models;

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
     * Relation to k8s credentials.
     *
     * @return HasOne
     */
    public function k8sCredentials(): HasOne
    {
        return $this->hasOne(ClusterK8sCredential::class, 'cluster_id', 'id');
    }

    /**
     * Relation to git credentials.
     *
     * @return HasOne
     */
    public function gitCredentials(): HasOne
    {
        return $this->hasOne(ClusterGitCredential::class, 'cluster_id', 'id');
    }

    /**
     * Relation to resources.
     *
     * @return HasMany
     */
    public function resources(): HasMany
    {
        return $this->hasMany(ClusterResource::class, 'cluster_id', 'id');
    }

    /**
     * Relation to namespaces.
     *
     * @return HasMany
     */
    public function namespaces(): HasMany
    {
        return $this->hasMany(ClusterNamespace::class, 'cluster_id', 'id');
    }
}
