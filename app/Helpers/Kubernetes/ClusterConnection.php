<?php

declare(strict_types=1);

namespace App\Helpers\Kubernetes;

use Illuminate\Support\Facades\Storage;
use RenokiCo\PhpK8s\KubernetesCluster;

/**
 * @see https://php-k8s.renoki.org/
 */
class ClusterConnection
{
    public static $connection;

    public static function open()
    {
        $kubeconfigDeployed = Storage::disk('local')->put('kubeconfig.yaml', config('flux.cluster.authentication'));
        $kubeconfigPath     = Storage::disk('local')->path('kubeconfig.yaml');

        self::$connection = KubernetesCluster::fromKubeConfigYamlFile($kubeconfigPath, 'default');

        return self::$connection;
    }

    public static function get()
    {
        return self::$connection;
    }

    public static function close()
    {
        self::$connection = null;
    }
}
