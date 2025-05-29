<?php

declare(strict_types=1);

namespace App\Models\Projects\Projects;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Project.
 *
 * This class is the model for projects.
 *
 * @OA\Schema(
 *     schema="Project",
 *     type="object",
 *
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="user_id", type="integer", format="int64", example="1"),
 *     @OA\Property(property="name", type="string", example="Project 1"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 * )
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
    use HasFactory;

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
     * Get the all statistics attribute.
     *
     * @return array
     */
    public static function allStatistics(): array
    {
        $projects = Project::all();

        $clusterStatistics = $projects->map(function (Project $project) {
            return $project->clusterStatistics;
        });

        return [
            'metrics' => [
                'capacity' => [
                    'cpu'     => (clone $clusterStatistics)->sum('metrics.capacity.cpu'),
                    'storage' => (clone $clusterStatistics)->sum('metrics.capacity.storage'),
                    'memory'  => (clone $clusterStatistics)->sum('metrics.capacity.memory'),
                    'pods'    => (clone $clusterStatistics)->sum('metrics.capacity.pods'),
                ],
                'usage' => [
                    'cpu'     => (clone $clusterStatistics)->sum('metrics.usage.cpu'),
                    'storage' => (clone $clusterStatistics)->sum('metrics.usage.storage'),
                    'memory'  => (clone $clusterStatistics)->sum('metrics.usage.memory'),
                    'pods'    => (clone $clusterStatistics)->sum('metrics.usage.pods'),
                ],
                'utilization' => [
                    'cpu'     => (clone $clusterStatistics)->sum('metrics.utilization.cpu'),
                    'storage' => (clone $clusterStatistics)->sum('metrics.utilization.storage'),
                    'memory'  => (clone $clusterStatistics)->sum('metrics.utilization.memory'),
                    'pods'    => (clone $clusterStatistics)->sum('metrics.utilization.pods'),
                ],
            ],
            'alerts' => [
                'warning' => [
                    'cpu'     => (clone $clusterStatistics)->contains('alerts.warning.cpu', true) || (clone $clusterStatistics)->contains(null),
                    'storage' => (clone $clusterStatistics)->contains('alerts.warning.storage', true) || (clone $clusterStatistics)->contains(null),
                    'memory'  => (clone $clusterStatistics)->contains('alerts.warning.memory', true) || (clone $clusterStatistics)->contains(null),
                    'pods'    => (clone $clusterStatistics)->contains('alerts.warning.pods', true) || (clone $clusterStatistics)->contains(null),
                ],
                'critical' => [
                    'cpu'     => (clone $clusterStatistics)->contains('alerts.critical.cpu', true) || (clone $clusterStatistics)->contains(null),
                    'storage' => (clone $clusterStatistics)->contains('alerts.critical.storage', true) || (clone $clusterStatistics)->contains(null),
                    'memory'  => (clone $clusterStatistics)->contains('alerts.critical.memory', true) || (clone $clusterStatistics)->contains(null),
                    'pods'    => (clone $clusterStatistics)->contains('alerts.critical.pods', true) || (clone $clusterStatistics)->contains(null),
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
