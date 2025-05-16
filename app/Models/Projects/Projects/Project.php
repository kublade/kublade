<?php

declare(strict_types=1);

namespace App\Models\Projects\Projects;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Project.
 *
 * This class is the model for projects.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Project extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'projects';

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
     * Relation to clusters.
     *
     * @return HasMany
     */
    public function clusters(): HasMany
    {
        return $this->hasMany(Cluster::class, 'project_id', 'id');
    }

    /**
     * Relation to deployments.
     *
     * @return HasMany
     */
    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class, 'project_id', 'id');
    }

    /**
     * Get the statistics attribute.
     *
     * @return array
     */
    public function getClusterStatisticsAttribute(): array
    {
        $clusterMetrics = $this->clusters->map(function (Cluster $cluster) {
            return $cluster->statistics;
        });

        return [
            'metrics' => [
                'capacity' => [
                    'cpu'     => (clone $clusterMetrics)->sum('metrics.capacity.cpu'),
                    'storage' => (clone $clusterMetrics)->sum('metrics.capacity.storage'),
                    'memory'  => (clone $clusterMetrics)->sum('metrics.capacity.memory'),
                    'pods'    => (clone $clusterMetrics)->sum('metrics.capacity.pods'),
                ],
                'usage' => [
                    'cpu'     => (clone $clusterMetrics)->sum('metrics.usage.cpu'),
                    'storage' => (clone $clusterMetrics)->sum('metrics.usage.storage'),
                    'memory'  => (clone $clusterMetrics)->sum('metrics.usage.memory'),
                    'pods'    => (clone $clusterMetrics)->sum('metrics.usage.pods'),
                ],
                'utilization' => [
                    'cpu'     => (clone $clusterMetrics)->sum('metrics.utilization.cpu'),
                    'storage' => (clone $clusterMetrics)->sum('metrics.utilization.storage'),
                    'memory'  => (clone $clusterMetrics)->sum('metrics.utilization.memory'),
                    'pods'    => (clone $clusterMetrics)->sum('metrics.utilization.pods'),
                ],
            ],
            'alerts' => [
                'warning' => [
                    'cpu'     => (clone $clusterMetrics)->contains('alerts.warning.cpu', true) || (clone $clusterMetrics)->contains(null),
                    'storage' => (clone $clusterMetrics)->contains('alerts.warning.storage', true) || (clone $clusterMetrics)->contains(null),
                    'memory'  => (clone $clusterMetrics)->contains('alerts.warning.memory', true) || (clone $clusterMetrics)->contains(null),
                    'pods'    => (clone $clusterMetrics)->contains('alerts.warning.pods', true) || (clone $clusterMetrics)->contains(null),
                ],
                'critical' => [
                    'cpu'     => (clone $clusterMetrics)->contains('alerts.critical.cpu', true) || (clone $clusterMetrics)->contains(null),
                    'storage' => (clone $clusterMetrics)->contains('alerts.critical.storage', true) || (clone $clusterMetrics)->contains(null),
                    'memory'  => (clone $clusterMetrics)->contains('alerts.critical.memory', true) || (clone $clusterMetrics)->contains(null),
                    'pods'    => (clone $clusterMetrics)->contains('alerts.critical.pods', true) || (clone $clusterMetrics)->contains(null),
                ],
            ],
        ];
    }

    /**
     * Get the deployment statistics attribute.
     *
     * @return array
     */
    public function getDeploymentStatisticsAttribute(): array
    {
        $deploymentStatistics = $this->deployments->map(function (Deployment $deployment) {
            return $deployment->statistics;
        })->filter(function ($deployment) {
            return $deployment !== null;
        });

        if ($deploymentStatistics->isEmpty()) {
            return [
                'cpu'     => null,
                'memory'  => null,
                'storage' => null,
            ];
        }

        return [
            'cpu'     => $deploymentStatistics->sum('cpu'),
            'memory'  => $deploymentStatistics->sum('memory'),
            'storage' => $deploymentStatistics->sum('storage'),
        ];
    }
}
