<?php

declare(strict_types=1);

namespace App\Jobs\Cluster\Actions;

use App\Helpers\CpuUtilization;
use App\Helpers\Filesize;
use App\Helpers\Kubernetes\ClusterConnection;
use App\Jobs\Base\Job;
use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Kubernetes\Resources\ContainerAdvisoryMetric;
use App\Models\Kubernetes\Resources\Node;
use App\Models\Kubernetes\Resources\NodeMetric;
use App\Models\Kubernetes\Resources\NodeSpec;
use App\Models\Kubernetes\Resources\NodeSpecCidr;
use App\Models\Kubernetes\Resources\NodeStatusAddress;
use App\Models\Kubernetes\Resources\NodeStatusAllocatable;
use App\Models\Kubernetes\Resources\NodeStatusCapacity;
use App\Models\Kubernetes\Resources\NodeStatusCondition;
use App\Models\Kubernetes\Resources\NodeStatusDaemonEndpoint;
use App\Models\Kubernetes\Resources\NodeStatusImage;
use App\Models\Kubernetes\Resources\NodeStatusImageName;
use App\Models\Kubernetes\Resources\NodeStatusNodeInfo;
use App\Models\Kubernetes\Resources\NodeStatusVolumeAttachment;
use App\Models\Kubernetes\Resources\NodeStatusVolumeUse;
use App\Models\Kubernetes\Resources\Ns;
use App\Models\Kubernetes\Resources\PersistentVolume;
use App\Models\Kubernetes\Resources\PersistentVolumeSpec;
use App\Models\Kubernetes\Resources\Pod;
use App\Models\Kubernetes\Resources\PodLog;
use App\Models\Kubernetes\Resources\PodMetric;
use App\Models\Kubernetes\Resources\PodMetricContainer;
use App\Models\Kubernetes\Resources\PodSpec;
use App\Models\Kubernetes\Resources\PodVolume;
use App\Models\Kubernetes\Resources\Service;
use App\Models\Kubernetes\Resources\ServicePort;
use App\Models\Projects\Deployments\Deployment;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use RenokiCo\PhpK8s\Kinds\K8sNode;
use RenokiCo\PhpK8s\Kinds\K8sPod;

/**
 * Class StatusMonitoring.
 *
 * This class is the dispatcher job for kubernetes cluster monitoring.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class StatusMonitoring extends Job implements ShouldBeUnique
{
    public static $onQueue = 'cluster_status_monitoring';

    private string $cluster_id;

    /**
     * StatusMonitoring constructor.
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

        if (!$cluster) {
            return;
        }

        ClusterConnection::open($cluster);

        $api = ClusterConnection::get();

        $nodes          = collect($api->node()->all()->toArray());
        $nodeMetricList = collect(
            $api
                ->setResourceClass(K8sNode::class)
                ->runOperation(
                    'GET_OP',
                    '/apis/metrics.k8s.io/v1beta1/nodes',
                    '',
                    [
                        'pretty' => 1,
                    ]
                )
                ->toArray()
        );

        $objects = [
            'namespace' => collect(),
            'node'      => collect(),
            'pod'       => collect(),
        ];

        $namespaces = collect($api->namespace()->all()->toArray());

        $namespaces->each(function ($data) use ($cluster, &$objects) {
            $namespaceDeployment = Deployment::where('uuid', '=', $data['metadata']['name'])->first();

            if ($namespaceDeployment) {
                $namespace = Ns::updateOrCreate([
                    'cluster_id' => $cluster->id,
                    'uuid'       => $data['metadata']['uid'],
                ], [
                    'deployment_id'        => $namespaceDeployment?->id,
                    'api_version'          => $data['apiVersion'],
                    'name'                 => $data['metadata']['name'],
                    'resource_version'     => $data['metadata']['resourceVersion'],
                    'namespace_created_at' => Carbon::parse($data['metadata']['creationTimestamp']),
                ]);

                $objects['namespace']->push($namespace);
            }
        });

        $nodes->each(function ($data) use ($cluster, $nodeMetricList, &$objects) {
            $node = Node::updateOrCreate([
                'cluster_id' => $cluster->id,
                'uuid'       => $data['metadata']['uid'],
            ], [
                'api_version'      => $data['apiVersion'],
                'name'             => $data['metadata']['name'],
                'resource_version' => $data['metadata']['resourceVersion'],
                'node_created_at'  => Carbon::parse($data['metadata']['creationTimestamp']),
            ]);

            $objects['node']->push($node);

            $nodeMetrics = $nodeMetricList->filter(function ($metric) use ($data) {
                return $metric['metadata']['name'] === $data['metadata']['name'];
            })->first();

            if ($nodeMetrics) {
                $nodeMetric = NodeMetric::updateOrCreate([
                    'node_id'           => $node->id,
                    'cpu_usage'         => $nodeMetrics['usage']['cpu'],
                    'memory_usage'      => $nodeMetrics['usage']['memory'],
                    'metric_created_at' => Carbon::parse($nodeMetrics['metadata']['creationTimestamp']),
                ]);
            }

            $nodeSpec = NodeSpec::updateOrCreate([
                'node_id'     => $node->id,
                'provider_id' => array_key_exists('providerID', $data['spec']) ? $data['spec']['providerID'] : null,
            ]);

            collect($data['spec']['podCIDRs'] ?? [])->each(function ($cidr) use ($nodeSpec) {
                $nodeSpecCidr = NodeSpecCidr::updateOrCreate([
                    'node_spec_id' => $nodeSpec->id,
                    'cidr'         => $cidr,
                ]);
            });

            $nodeStatusCapacity = NodeStatusCapacity::updateOrCreate([
                'node_id'           => $node->id,
                'cpu'               => CpuUtilization::toCore($data['status']['capacity']['cpu']),
                'ephemeral_storage' => Filesize::bytesFromString($data['status']['capacity']['ephemeral-storage']),
                'hugepages_1gi'     => $data['status']['capacity']['hugepages-1Gi'],
                'hugepages_2mi'     => $data['status']['capacity']['hugepages-2Mi'],
                'memory'            => Filesize::bytesFromString($data['status']['capacity']['memory']),
                'pods'              => $data['status']['capacity']['pods'],
            ]);

            $nodeStatusAllocatable = NodeStatusAllocatable::updateOrCreate([
                'node_id'           => $node->id,
                'cpu'               => CpuUtilization::toCore($data['status']['allocatable']['cpu']),
                'ephemeral_storage' => Filesize::bytesFromString($data['status']['allocatable']['ephemeral-storage']),
                'hugepages_1gi'     => $data['status']['allocatable']['hugepages-1Gi'],
                'hugepages_2mi'     => $data['status']['allocatable']['hugepages-2Mi'],
                'memory'            => Filesize::bytesFromString($data['status']['allocatable']['memory']),
                'pods'              => $data['status']['allocatable']['pods'],
            ]);

            collect($data['status']['conditions'] ?? [])->each(function ($condition) use ($node) {
                $nodeStatusCondition = NodeStatusCondition::updateOrCreate([
                    'node_id'              => $node->id,
                    'type'                 => $condition['type'],
                    'status'               => $condition['status'],
                    'last_heartbeat_time'  => Carbon::parse($condition['lastHeartbeatTime']),
                    'last_transition_time' => Carbon::parse($condition['lastTransitionTime']),
                    'reason'               => $condition['reason'],
                    'message'              => $condition['message'],
                ]);
            });

            collect($data['status']['addresses'] ?? [])->each(function ($address) use ($node) {
                $nodeStatusAddress = NodeStatusAddress::updateOrCreate([
                    'node_id' => $node->id,
                    'type'    => $address['type'],
                    'address' => $address['address'],
                ]);
            });

            collect($data['status']['daemonEndpoints'] ?? [])->each(function ($config, $name) use ($node) {
                $nodeStatusDaemonEndpoint = NodeStatusDaemonEndpoint::updateOrCreate([
                    'node_id' => $node->id,
                    'name'    => $name,
                    'port'    => $config['Port'],
                ]);
            });

            $nodeStatusNodeInfo = NodeStatusNodeInfo::updateOrCreate([
                'node_id'                   => $node->id,
                'machine_id'                => $data['status']['nodeInfo']['machineID'],
                'system_uuid'               => $data['status']['nodeInfo']['systemUUID'],
                'boot_id'                   => $data['status']['nodeInfo']['bootID'],
                'kernel_version'            => $data['status']['nodeInfo']['kernelVersion'],
                'os_image'                  => $data['status']['nodeInfo']['osImage'],
                'container_runtime_version' => $data['status']['nodeInfo']['containerRuntimeVersion'],
                'kubelet_version'           => $data['status']['nodeInfo']['kubeletVersion'],
                'kube_proxy_version'        => $data['status']['nodeInfo']['kubeProxyVersion'],
                'operating_system'          => $data['status']['nodeInfo']['operatingSystem'],
                'architecture'              => $data['status']['nodeInfo']['architecture'],
            ]);

            collect($data['status']['images'] ?? [])->each(function ($data) use ($node) {
                $nodeStatusImage = NodeStatusImage::updateOrCreate([
                    'node_id'    => $node->id,
                    'size_bytes' => $data['sizeBytes'],
                ]);

                collect($data['names'] ?? [])->each(function ($name) use ($nodeStatusImage) {
                    $nodeStatusImageName = NodeStatusImageName::updateOrCreate([
                        'node_image_id' => $nodeStatusImage->id,
                        'name'          => $name,
                    ]);
                });
            });

            collect($data['status']['volumesInUse'] ?? [])->each(function ($name) use ($node) {
                $nodeStatusVolumeUse = NodeStatusVolumeUse::updateOrCreate([
                    'node_id' => $node->id,
                    'name'    => $name,
                ]);
            });

            collect($data['status']['volumesAttached'] ?? [])->each(function ($attachment) use ($node) {
                $nodeStatusVolumeAttached = NodeStatusVolumeAttachment::updateOrCreate([
                    'node_id'     => $node->id,
                    'name'        => $attachment['name'],
                    'device_path' => $attachment['devicePath'],
                ]);
            });
        });

        $volumes = collect($api->persistentVolume()->get()->toArray());

        $objects['namespace']->each(function ($namespace) use ($api, $objects, $volumes) {
            $volumeClaims = collect($api->persistentVolumeClaim()->whereNamespace($namespace->name)->get()->toArray())->map(function ($volumeClaim) {
                return $volumeClaim['spec']['volumeName'];
            });

            (clone $volumes)
                ->filter(function ($volume) use ($volumeClaims) {
                    return $volumeClaims->contains($volume['metadata']['name']);
                })
                ->each(function ($volume) use ($namespace) {
                    $namespaceVolume = PersistentVolume::updateOrCreate([
                        'namespace_id' => $namespace->id,
                        'uuid'         => $volume['metadata']['uid'],
                    ], [
                        'name'              => $volume['metadata']['name'],
                        'resource_version'  => $volume['metadata']['resourceVersion'],
                        'volume_created_at' => $volume['metadata']['creationTimestamp'],
                    ]);

                    $persistentVolumeSpec = PersistentVolumeSpec::updateOrCreate([
                        'persistent_volume_id'             => $namespaceVolume->id,
                        'capacity'                         => $volume['spec']['capacity']['storage'],
                        'driver'                           => $volume['spec']['csi']['driver'],
                        'volume_handle'                    => $volume['spec']['csi']['volumeHandle'],
                        'filesystem_type'                  => $volume['spec']['csi']['fsType'],
                        'claim_kind'                       => $volume['spec']['claimRef']['kind'],
                        'claim_namespace'                  => $volume['spec']['claimRef']['namespace'],
                        'claim_name'                       => $volume['spec']['claimRef']['name'],
                        'claim_uuid'                       => $volume['spec']['claimRef']['uid'],
                        'claim_api_version'                => $volume['spec']['claimRef']['apiVersion'],
                        'claim_resource_version'           => $volume['spec']['claimRef']['resourceVersion'],
                        'persistent_volume_reclaim_policy' => $volume['spec']['persistentVolumeReclaimPolicy'],
                        'volume_mode'                      => $volume['spec']['volumeMode'],
                        'phase'                            => $volume['status']['phase'],
                    ]);
                });

            $pods = $api->pod()->whereNamespace($namespace->name)->get();

            $pods->each(function ($podObject) use ($api, $namespace, &$objects) {
                $pod = $podObject->toArray();

                $namespacePod = Pod::updateOrCreate([
                    'namespace_id' => $namespace->id,
                    'name'         => $pod['metadata']['name'],
                ], [
                    'api_version'      => $pod['apiVersion'],
                    'resource_version' => $pod['metadata']['resourceVersion'],
                    'pod_created_at'   => Carbon::parse($pod['metadata']['creationTimestamp']),
                ]);

                $objects['pod']->push($namespacePod);

                try {
                    $podLogString = $podObject->logs();

                    $podLog = PodLog::updateOrCreate([
                        'pod_id' => $namespacePod->id,
                    ], [
                        'logs' => $podLogString,
                    ]);
                } catch (Exception $exception) {
                }

                try {
                    $podMetrics = $api
                        ->setResourceClass(K8sPod::class)
                        ->runOperation(
                            'GET_OP',
                            '/apis/metrics.k8s.io/v1beta1/namespaces/' . $namespace->name . '/pods/' . $pod['metadata']['name'],
                            '',
                            [
                                'pretty' => 1,
                            ]
                        )
                        ->toArray();

                    if ($podMetrics) {
                        $podMetric = PodMetric::updateOrCreate([
                            'pod_id' => $namespacePod->id,
                            'name'   => $podMetrics['metadata']['name'],
                        ]);

                        collect($podMetrics['containers'] ?? [])->each(function ($container) use ($podMetric, $podMetrics) {
                            $podMetricContainer = PodMetricContainer::updateOrCreate([
                                'pod_metric_id'     => $podMetric->id,
                                'name'              => $container['name'],
                                'cpu_usage'         => $container['usage']['cpu'],
                                'memory_usage'      => $container['usage']['memory'],
                                'metric_created_at' => Carbon::parse($podMetrics['metadata']['creationTimestamp']),
                            ]);
                        });
                    }
                } catch (Exception $exception) {
                }

                $podSpec = PodSpec::updateOrCreate([
                    'pod_id'                           => $namespacePod->id,
                    'restart_policy'                   => $pod['spec']['restartPolicy'],
                    'termination_grace_period_seconds' => $pod['spec']['terminationGracePeriodSeconds'],
                    'dns_policy'                       => $pod['spec']['dnsPolicy'],
                    'service_account_name'             => array_key_exists('serviceAccountName', $pod['spec']) ? $pod['spec']['serviceAccountName'] : null,
                    'service_account'                  => array_key_exists('serviceAccount', $pod['spec']) ? $pod['spec']['serviceAccount'] : null,
                    'node_name'                        => array_key_exists('nodeName', $pod['spec']) ? $pod['spec']['nodeName'] : null,
                    'scheduler_name'                   => $pod['spec']['schedulerName'],
                    'priority'                         => $pod['spec']['priority'],
                    'enable_service_links'             => $pod['spec']['enableServiceLinks'],
                    'preemption_policy'                => $pod['spec']['preemptionPolicy'],
                ]);

                collect($pod['spec']['volumes'] ?? [])->each(function ($persistentVolume) use ($namespacePod) {
                    if (
                        array_key_exists('persistentVolumeClaim', $persistentVolume) &&
                        array_key_exists('claimName', $persistentVolume['persistentVolumeClaim'])
                    ) {
                        $persistentVolumeClaim = PersistentVolumeSpec::where('claim_name', '=', $persistentVolume['persistentVolumeClaim']['claimName'])->first();

                        if ($persistentVolumeClaim) {
                            $podVolume = PodVolume::updateOrCreate([
                                'pod_id'               => $namespacePod->id,
                                'persistent_volume_id' => $persistentVolumeClaim->id,
                            ]);
                        }
                    }
                });
            });

            $services = $api->service()->whereNamespace($namespace->name)->get();

            $services->each(function ($serviceObject) use ($namespace, &$objects) {
                $service   = $serviceObject->toArray();
                $ipAddress = null;

                if (
                    array_key_exists('status', $service) &&
                    array_key_exists('loadBalancer', $service['status']) &&
                    array_key_exists('ingress', $service['status']['loadBalancer'])
                ) {
                    $ingress = collect($service['status']['loadBalancer']['ingress'] ?? [])->first();

                    if (array_key_exists('ip', $ingress)) {
                        $ipAddress = $ingress['ip'];
                    }
                }

                $namespaceService = Service::updateOrCreate([
                    'namespace_id' => $namespace->id,
                    'uuid'         => $service['metadata']['uid'],
                ], [
                    'name'               => $service['metadata']['name'],
                    'public_ip'          => $ipAddress,
                    'resource_version'   => $service['metadata']['resourceVersion'],
                    'service_created_at' => Carbon::parse($service['metadata']['creationTimestamp']),
                ]);

                collect($service['spec']['ports'] ?? [])->each(function ($port) use ($namespaceService) {
                    $servicePort = ServicePort::updateOrCreate([
                        'service_id' => $namespaceService->id,
                        'name'       => $port['name'],
                    ], [
                        'protocol'    => $port['protocol'],
                        'port'        => $port['port'],
                        'target_port' => $port['targetPort'],
                    ]);
                });
            });
        });

        $objects['node']->each(function ($node) use ($objects) {
            ClusterConnection::proxyCall('/api/v1/nodes/' . $node->name . '/proxy/metrics/cadvisor', 'container_network_', ['bond0', 'bond0.4007', 'eth0'])
                ->filter(function ($containerAdvisorMetric) {
                    return in_array($containerAdvisorMetric['identifier'], [
                        'container_network_receive_bytes_total',
                        'container_network_transmit_bytes_total',
                    ]);
                })
                ->map(function ($containerAdvisorMetric) use ($objects) {
                    $namespaces = $objects['namespace']->filter(function ($namespace) use ($containerAdvisorMetric) {
                        return $namespace->name === $containerAdvisorMetric['meta']['namespace'];
                    });

                    if ($namespaces->isNotEmpty()) {
                        $containerAdvisorMetric['meta']['namespace'] = $namespaces->first();
                    } else {
                        unset($containerAdvisorMetric['meta']['namespace']);

                        return null;
                    }

                    $pods = $objects['pod']->filter(function ($pod) use ($containerAdvisorMetric) {
                        return $pod->name === $containerAdvisorMetric['meta']['pod'];
                    });

                    if ($pods->isNotEmpty()) {
                        $containerAdvisorMetric['meta']['pod'] = $pods->first();
                    } else {
                        unset($containerAdvisorMetric['meta']['pod']);

                        return null;
                    }

                    if (empty($containerAdvisorMetric['meta']['interface'])) {
                        return null;
                    }

                    return $containerAdvisorMetric;
                })
                ->filter(function ($containerAdvisorMetric) {
                    return isset($containerAdvisorMetric);
                })
                ->each(function ($containerAdvisorMetric) use ($node) {
                    $metric = ContainerAdvisoryMetric::create([
                        'namespace_id' => $containerAdvisorMetric['meta']['namespace']->id,
                        'node_id'      => $node->id,
                        'pod_id'       => $containerAdvisorMetric['meta']['pod']->id,
                        'key'          => $containerAdvisorMetric['identifier'],
                        'interface'    => $containerAdvisorMetric['meta']['interface'],
                        'value'        => $containerAdvisorMetric['data'],
                    ]);
                });
        });

        ClusterConnection::close();
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
            'job:cluster:' . $this->cluster_id . ':action:StatusMonitoring',
        ];
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'cluster-status-monitoring-' . $this->cluster_id;
    }
}
