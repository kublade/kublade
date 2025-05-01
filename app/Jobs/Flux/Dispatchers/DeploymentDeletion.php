<?php

declare(strict_types=1);

namespace App\Jobs\Flux\Dispatchers;

use App\Jobs\Base\Job;
use App\Jobs\Flux\Actions\DeploymentDeletion as DeploymentDeletionJob;
use App\Models\Kubernetes\Clusters\Status;
use App\Models\Projects\Deployments\Deployment;
use Carbon\Carbon;

/**
 * Class DeploymentDeletion.
 *
 * This class is the dispatcher job for processing flux deployment deletion.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentDeletion extends Job
{
    public static $onQueue = 'dispatchers';

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        Deployment::whereNotNull('deployed_at')
            ->whereNotNull('creation_dispatched_at')
            ->whereNull('deletion_dispatched_at')
            ->where('delete', '=', true)
            ->each(function (Deployment $deployment) {
                if ($deployment->cluster->status === Status::STATUS_OFFLINE) {
                    return;
                }

                $this->dispatch((new DeploymentDeletionJob([
                    'deployment_id' => $deployment->id,
                ]))->onQueue('flux_deployment'));

                $deployment->update([
                    'deletion_dispatched_at' => Carbon::now(),
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
            'job:flux:dispatcher',
            'job:flux:dispatcher:DeploymentDeletion',
        ];
    }
}
