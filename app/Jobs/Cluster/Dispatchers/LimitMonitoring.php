<?php

declare(strict_types=1);

namespace App\Jobs\Cluster\Dispatchers;

use App\Jobs\Base\Job;
use App\Jobs\Cluster\Actions\LimitMonitoring as LimitMonitoringJob;
use App\Models\Kubernetes\Clusters\Cluster;
use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * Class LimitMonitoring.
 *
 * This class is the dispatcher job for processing cluster limit monitoring.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class LimitMonitoring extends Job implements ShouldBeUnique
{
    public static $onQueue = 'dispatchers';

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        Cluster::query()->each(function ($cluster) {
            $this->dispatch((new LimitMonitoringJob([
                'cluster_id' => $cluster->id,
            ]))->onQueue('dispatchers'));
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
            'job:cluster',
            'job:cluster:dispatcher',
            'job:cluster:dispatcher:LimitMonitoring',
        ];
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'cluster-limit-monitoring';
    }
}
