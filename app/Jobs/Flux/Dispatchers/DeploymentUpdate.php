<?php

declare(strict_types=1);

namespace App\Jobs\Flux\Dispatchers;

use App\Jobs\Base\Job;
use App\Jobs\Flux\Actions\DeploymentUpdate as DeploymentUpdateJob;
use App\Models\Kubernetes\Clusters\Status;
use App\Models\Projects\Deployments\Deployment;
use Carbon\Carbon;

/**
 * Class DeploymentUpdate.
 *
 * This class is the dispatcher job for processing flux deployment updates.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentUpdate extends Job
{
    public static $onQueue = 'dispatchers';

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        Deployment::whereNotNull('deployed_at')
            ->whereNotNull('creation_dispatched_at')
            ->where('update', '=', true)
            ->where('delete', '=', false)
            ->whereNotNull('approved_at')
            ->each(function (Deployment $deployment) {
                if ($deployment->cluster->status === Status::STATUS_OFFLINE) {
                    return;
                }

                $this->dispatch((new DeploymentUpdateJob([
                    'deployment_id' => $deployment->id,
                ]))->onQueue('flux_deployment'));

                $deployment->update([
                    'approved_at'          => null,
                    'update'               => false,
                    'update_dispatched_at' => Carbon::now(),
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
            'job:flux:dispatcher:DeploymentUpdate',
        ];
    }
}
