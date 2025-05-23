<?php

declare(strict_types=1);

namespace App\Models\Kubernetes\Clusters;

use App\Models\Kubernetes\Metrics\ClusterMetric;
use App\Models\Kubernetes\Metrics\ClusterMetricNode;
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
 * @OA\Schema(
 *     schema="Cluster",
 *     type="object",
 *
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="user_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="project_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="name", type="string", example="Cluster 1"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 * )
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
     * Relation to metrics.
     *
     * @return HasMany
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(ClusterMetric::class, 'cluster_id', 'id');
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

    /**
     * Get the statistics attribute.
     *
     * @return array
     */
    public function getStatisticsAttribute(): array | null
    {
        $clusterMetric = $this->metrics()->orderByDesc('created_at')->first();
        $warning       = $this->resources()->where('type', '=', Resource::TYPE_ALERT)->first();
        $limit         = $this->resources()->where('type', '=', Resource::TYPE_LIMIT)->first();

        if (! $clusterMetric) {
            return null;
        }

        return [
            'metrics' => [
                'capacity' => [
                    'cpu'     => $clusterMetric->capacity->cpu * 100,
                    'storage' => $clusterMetric->capacity->storage / 1024 / 1024 / 1024,
                    'memory'  => $clusterMetric->capacity->memory / 1024 / 1024 / 1024,
                    'pods'    => $clusterMetric->capacity->pods,
                ],
                'usage' => [
                    'cpu'     => $clusterMetric->usage->cpu * 100,
                    'storage' => $clusterMetric->usage->storage / 1024 / 1024 / 1024,
                    'memory'  => $clusterMetric->usage->memory / 1024 / 1024 / 1024,
                    'pods'    => $clusterMetric->usage->pods,
                ],
                'utilization' => [
                    'cpu'     => $clusterMetric->utilization->cpu,
                    'storage' => $clusterMetric->utilization->storage,
                    'memory'  => $clusterMetric->utilization->memory,
                    'pods'    => $clusterMetric->utilization->pods,
                ],
                'alerts' => [
                    'warning' => $warning ? [
                        'cpu'     => $clusterMetric->utilization->cpu >= $warning->cpu,
                        'storage' => $clusterMetric->utilization->storage >= $warning->storage,
                        'memory'  => $clusterMetric->utilization->memory >= $warning->memory,
                        'pods'    => $clusterMetric->utilization->pods >= $warning->pods,
                    ] : [
                        'cpu'     => false,
                        'storage' => false,
                        'memory'  => false,
                        'pods'    => false,
                    ],
                    'critical' => $limit ? [
                        'cpu'     => $clusterMetric->utilization->cpu >= $limit->cpu,
                        'storage' => $clusterMetric->utilization->storage >= $limit->storage,
                        'memory'  => $clusterMetric->utilization->memory >= $limit->memory,
                        'pods'    => $clusterMetric->utilization->pods >= $limit->pods,
                    ] : [
                        'cpu'     => false,
                        'storage' => false,
                        'memory'  => false,
                        'pods'    => false,
                    ],
                ],
            ],
            'nodes' => $clusterMetric->nodeMetrics->map(function (ClusterMetricNode $metric) use ($warning, $limit) {
                return [
                    'name'    => $metric->node->name,
                    'metrics' => [
                        'capacity' => [
                            'cpu'    => $metric->capacity->cpu * 100,
                            'memory' => $metric->capacity->memory / 1024 / 1024 / 1024,
                        ],
                        'usage' => [
                            'cpu'    => $metric->usage->cpu * 100,
                            'memory' => $metric->usage->memory / 1024 / 1024 / 1024,
                        ],
                        'utilization' => [
                            'cpu'    => $metric->utilization->cpu,
                            'memory' => $metric->utilization->memory,
                        ],
                        'alerts' => [
                            'warning' => $warning ? [
                                'cpu'    => $metric->utilization->cpu >= $warning->cpu,
                                'memory' => $metric->utilization->memory >= $warning->memory,
                            ] : [
                                'cpu'    => false,
                                'memory' => false,
                            ],
                            'critical' => $limit ? [
                                'cpu'    => $metric->utilization->cpu >= $limit->cpu,
                                'memory' => $metric->utilization->memory >= $limit->memory,
                            ] : [
                                'cpu'    => false,
                                'memory' => false,
                            ],
                        ],
                    ],
                ];
            })->toArray(),
        ];
    }
}
