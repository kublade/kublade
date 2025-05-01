<?php

declare(strict_types=1);

namespace App\Jobs\Cluster\Dispatchers;

use App\Jobs\Base\Job;
use App\Jobs\Cluster\Actions\StatusMonitoring as StatusMonitoringJob;
use App\Models\Kubernetes\Clusters\Cluster;
use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * Class StatusMonitoring.
 *
 * This class is the dispatcher job for processing cluster status monitoring.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class StatusMonitoring extends Job implements ShouldBeUnique
{
    public static $onQueue = 'dispatchers';

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        Cluster::query()->each(function (Cluster $cluster) {
            $this->dispatch((new StatusMonitoringJob([
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
            'job:cluster:dispatcher:StatusMonitoring',
        ];
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'cluster-status-monitoring';
    }
}
