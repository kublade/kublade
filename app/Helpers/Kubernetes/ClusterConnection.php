<?php

declare(strict_types=1);

namespace App\Helpers\Kubernetes;

use App\Exceptions\KubeletException;
use App\Models\Kubernetes\Clusters\Cluster;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RenokiCo\PhpK8s\KubernetesCluster;

/**
 * Class ClusterConnection.
 *
 * This class is the helper for the cluster connection.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @see https://php-k8s.renoki.org/
 */
class ClusterConnection
{
    /**
     * The cluster.
     *
     * @var Cluster|null
     */
    public static $cluster;

    /**
     * The connection to the cluster.
     *
     * @var KubernetesCluster|null
     */
    public static $connection;

    /**
     * Open the connection to the cluster.
     *
     * @param Cluster $cluster
     *
     * @return KubernetesCluster
     */
    public static function open(Cluster $cluster): KubernetesCluster
    {
        self::$cluster    = $cluster;
        self::$connection = KubernetesCluster::fromKubeConfigYaml($cluster->k8sCredentials->kubeconfig, 'default');

        return self::$connection;
    }

    /**
     * Get the connection to the cluster.
     *
     * @return KubernetesCluster|null
     */
    public static function get(): ?KubernetesCluster
    {
        return self::$connection;
    }

    /**
     * Close the connection to the cluster.
     */
    public static function close(): void
    {
        self::$connection = null;
        self::$cluster    = null;
    }

    /**
     * Get the proxy call.
     *
     * @param string $path
     * @param string $filter
     * @param array  $interfaces
     *
     * @return Collection
     */
    public static function proxyCall(string $path, string $filter = 'container_', array $interfaces = ['bond0', 'bond0.4007', 'eth0']): Collection
    {
        if (!self::$cluster?->k8sCredentials) {
            throw new KubeletException('Bad Request', 400);
        }

        try {
            if ($token = self::$cluster->k8sCredentials->service_account_token) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])
                    ->withOptions([
                        'verify' => false,
                    ])
                    ->get(self::$cluster->k8sCredentials->api_url . $path)
                    ->body();
            } else {
                $response = Http::withOptions([
                    'verify' => false,
                ])
                    ->get(self::$cluster->k8sCredentials->api_url . $path)
                    ->body();
            }
        } catch (Exception $exception) {
            throw new KubeletException('Server Error', 500);
        }

        if (!$response) {
            throw new KubeletException('Server Error', 500);
        }

        return collect(explode("\n", $response))
            ->filter(function ($line) use ($filter, $interfaces) {
                preg_match('/(?<=interface=")(.[^"]*?)(?="[,}])/', $line, $interface);

                return str_starts_with($line, $filter ?? 'container_') &&
                    collect($interfaces)->filter(function ($filter) use ($interface) {
                        return in_array($filter, $interface);
                    })->isNotEmpty();
            })
            ->map(function ($line) {
                $identifier     = explode('{', $line)[0];
                $explodedString = explode('}', $line);
                $data           = substr(end($explodedString), 1);
                preg_match('/(?<=pod=")(.[^"]*?)(?="[,}])/', $line, $pods);
                preg_match('/(?<=namespace=")(.[^"]*?)(?="[,}])/', $line, $namespaces);
                preg_match('/(?<=interface=")(.[^"]*?)(?="[,}])/', $line, $interfaces);

                return [
                    'identifier' => $identifier,
                    'meta'       => [
                        'namespace' => collect($namespaces)
                            ->filter(function ($value) {
                                return $value !== '';
                            })
                            ->unique()
                            ->first(),
                        'pod' => collect($pods)
                            ->filter(function ($value) {
                                return $value !== '';
                            })
                            ->unique()
                            ->first(),
                        'interface' => collect($interfaces)
                            ->filter(function ($value) {
                                return $value !== '';
                            })
                            ->unique()
                            ->first(),
                    ],
                    'data' => doubleval(explode(' ', $data)[0]),
                ];
            })
            ->filter(function ($dataset) {
                return !(
                    empty($dataset['meta']['namespace']) &&
                    empty($dataset['meta']['pod']) &&
                    empty($dataset['meta']['interface'])
                );
            });
    }
}
