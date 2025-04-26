<?php

declare(strict_types=1);

namespace App\Jobs\Flux\Actions;

use App\Helpers\CpuUtilization;
use App\Helpers\Filesize;
use App\Jobs\Base\Job;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentMetric;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * Class StatusMonitoring.
 *
 * This class is the dispatcher job for flux deployment monitoring.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class StatusMonitoring extends Job implements ShouldBeUnique
{
    public $tries = 1;

    public $timeout = 3600;

    public static $onQueue = 'flux_status_monitoring';

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        Deployment::whereNotNull('deployed_at')->each(function ($deployment) {
            $cpuUsage     = 0;
            $memoryUsage  = 0;
            $storageUsage = 0;

            $deployment->namespaces->each(function ($namespace) use (&$storageUsage, &$memoryUsage, &$cpuUsage) {
                $namespace->persistentVolumes->each(function ($persistentVolume) use (&$storageUsage) {
                    $persistentVolume->specs->each(function ($spec) use (&$storageUsage) {
                        $format = substr($spec->capacity, -2);

                        if (!is_numeric($format)) {
                            $value = substr($spec->capacity, 0, -2);
                        } else {
                            $value  = $spec->capacity;
                            $format = 'B';
                        }

                        $storageUsage = $storageUsage + Filesize::bytesFromString($spec->capacity);
                    });
                });

                $namespace->pods->each(function ($pod) use (&$memoryUsage, &$cpuUsage) {
                    $count          = 0;
                    $memoryUsagePod = 0;
                    $cpuUsagePod    = 0;

                    $pod->metrics->each(function ($metric) use (&$memoryUsagePod, &$cpuUsagePod, &$count) {
                        $metric->podMetricContainers()
                            ->where('created_at', '>=', Carbon::now()->subHour())
                            ->each(function ($containerMetric) use (&$memoryUsagePod, &$cpuUsagePod, &$count) {
                                $memoryUsagePod = $memoryUsagePod + Filesize::bytesFromString($containerMetric->memory_usage);

                                if (is_numeric($cpuPercentage = CpuUtilization::toCore($containerMetric->cpu_usage))) {
                                    $cpuUsagePod = $cpuUsagePod + $cpuPercentage;
                                }

                                $count = $count + 1;
                            });
                    });

                    if ($count === 0) {
                        return true;
                    }

                    $memoryUsage += $memoryUsagePod / $count;
                    $cpuUsage += $cpuUsagePod / $count;
                });
            });

            DeploymentMetric::create([
                'deployment_id'  => $deployment->id,
                'storage_bytes'  => (int) $storageUsage,
                'memory_bytes'   => (int) $memoryUsage,
                'cpu_core_usage' => (float) $cpuUsage,
            ]);
        });
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
            'job:flux',
            'job:flux:action',
            'job:flux:action:StatusMonitoring',
        ];
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'flux-status-monitoring';
    }
}
