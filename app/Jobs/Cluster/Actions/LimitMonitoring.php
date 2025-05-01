<?php

declare(strict_types=1);

namespace App\Jobs\Cluster\Actions;

use App\Helpers\CpuUtilization;
use App\Helpers\Filesize;
use App\Jobs\Base\Job;
use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Kubernetes\Clusters\Status;
use App\Models\Kubernetes\Metrics\ClusterMetric;
use App\Models\Kubernetes\Metrics\ClusterMetricCapacity;
use App\Models\Kubernetes\Metrics\ClusterMetricNode;
use App\Models\Kubernetes\Metrics\ClusterMetricNodeCapacity;
use App\Models\Kubernetes\Metrics\ClusterMetricNodeUsage;
use App\Models\Kubernetes\Metrics\ClusterMetricNodeUtilization;
use App\Models\Kubernetes\Metrics\ClusterMetricUsage;
use App\Models\Kubernetes\Metrics\ClusterMetricUtilization;
use App\Models\Kubernetes\Resources\Node;
use App\Models\Projects\Deployments\Deployment;
use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * Class LimitMonitoring.
 *
 * This class is the dispatcher job for kubernetes limit monitoring.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class LimitMonitoring extends Job implements ShouldBeUnique
{
    public static $onQueue = 'cluster_limit_monitoring';

    private string $cluster_id;

    /**
     * LimitMonitoring constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->cluster_id = $data['cluster_id'];
    }

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        $cluster = Cluster::find($this->cluster_id);

        if (
            !$cluster ||
            $cluster->status === Status::STATUS_OFFLINE
        ) {
            return;
        }

        $clusterCapacity = [
            'cpu'     => 0,
            'storage' => 0,
            'memory'  => 0,
            'pods'    => 0,
        ];
        $clusterNodeMetrics = collect();
        $clusterUsage       = [
            'cpu'     => 0,
            'storage' => 0,
            'memory'  => 0,
            'pods'    => 0,
        ];

        $cluster->nodes()
            ->where('name', 'like', $cluster->node_prefix . '%')
            ->where('cluster_id', '=', $cluster->id)
            ->each(function (Node $node) use (&$clusterCapacity, &$clusterNodeMetrics, &$clusterUsage) {
                $nodeCapacity = $node->allocatables()
                    ->orderByDesc('created_at')
                    ->first();

                $clusterCapacity['cpu'] += $nodeCapacity->cpu;
                $clusterCapacity['storage'] += $nodeCapacity->ephemeral_storage;
                $clusterCapacity['memory'] += $nodeCapacity->memory;
                $clusterCapacity['pods'] += $nodeCapacity->pods;

                $nodeMetric = $node->metrics()
                    ->orderByDesc('created_at')
                    ->first();

                $nodeMetric = [
                    'cpu'    => CpuUtilization::toCore($nodeMetric->cpu_usage),
                    'memory' => Filesize::bytesFromString($nodeMetric->memory_usage),
                ];

                $clusterUsage['cpu'] += $nodeMetric['cpu'];
                $clusterUsage['memory'] += $nodeMetric['memory'];

                $nodeUtilization = [
                    'cpu'    => $nodeMetric['cpu'] / $nodeCapacity->cpu * 100,
                    'memory' => $nodeMetric['memory'] / $nodeCapacity->memory * 100,
                ];

                $clusterMetricNode = ClusterMetricNode::create([
                    'node_id' => $node->id,
                ]);
                $clusterMetricNodeCapacity = ClusterMetricNodeCapacity::create([
                    'cluster_metric_node_id' => $clusterMetricNode->id,
                    'cpu'                    => $nodeCapacity->cpu,
                    'memory'                 => $nodeCapacity->memory,
                ]);
                $clusterMetricNodeUsage = ClusterMetricNodeUsage::create([
                    'cluster_metric_node_id' => $clusterMetricNode->id,
                    ...$nodeMetric,
                ]);
                $clusterMetricNodeUtilization = ClusterMetricNodeUtilization::create([
                    'cluster_metric_node_id' => $clusterMetricNode->id,
                    ...$nodeUtilization,
                ]);

                $clusterNodeMetrics->push($clusterMetricNode->id);
            });

        $cluster->deployments()
            ->whereNotNull('deployed_at')
            ->where('delete', '=', false)
            ->where('cluster_id', '=', $cluster->id)
            ->each(function (Deployment $deployment) use (&$clusterUsage) {
                $deploymentMetric = $deployment->metrics()
                    ->orderByDesc('created_at')
                    ->first();

                $clusterUsage['storage'] += $deploymentMetric->storage_bytes;

                $pods = 0;

                $deployment->namespaces->each(function ($namespace) use (&$clusterUsage) {
                    $clusterUsage['pods'] += $namespace->pods()->count();
                });
            });

        $clusterMetric = ClusterMetric::create([]);

        ClusterMetricCapacity::create([
            'cluster_metric_id' => $clusterMetric->id,
            ...$clusterCapacity,
        ]);

        ClusterMetricUsage::create([
            'cluster_metric_id' => $clusterMetric->id,
            ...$clusterUsage,
        ]);

        ClusterMetricUtilization::create([
            'cluster_metric_id' => $clusterMetric->id,
            'cpu'               => $clusterUsage['cpu'] / $clusterCapacity['cpu'] * 100,
            'storage'           => $clusterUsage['storage'] / $clusterCapacity['storage'] * 100,
            'memory'            => $clusterUsage['memory'] / $clusterCapacity['memory'] * 100,
            'pods'              => $clusterUsage['pods'] / $clusterCapacity['pods'] * 100,
        ]);

        ClusterMetricNode::whereIn('id', $clusterNodeMetrics->toArray())->update([
            'cluster_metric_id' => $clusterMetric->id,
        ]);
    }

    /**
     * Define tags which the job can be identified by.
     *
     * @return array
     */
    public function tags(): array
    {
        return [
            'job',
            'job:cluster',
            'job:cluster:' . $this->cluster_id,
            'job:cluster:' . $this->cluster_id . ':action',
            'job:cluster:' . $this->cluster_id . ':action:LimitMonitoring',
        ];
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'cluster-limit-monitoring-' . $this->cluster_id;
    }
}
