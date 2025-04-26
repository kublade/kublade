<?php

declare(strict_types=1);

namespace App\Jobs\Flux\Actions;

use App\Jobs\Base\Job;
use App\Models\Projects\Deployments\Deployment;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * Class PhpmyadminDeploymentDeletion.
 *
 * This class is the action job for processing flux deployment deletion.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class PhpmyadminDeploymentDeletion extends Job implements ShouldBeUnique
{
    public $tries = 1;

    public $timeout = 3600;

    public $deployment_id;

    public static $onQueue = 'singletons';

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        Deployment::whereHas('deploymentPhpmyadminLinks')
            ->where('created_at', '<', Carbon::now()->subHours(config('phpmyadmin.deployment.lifetime.hours')))
            ->where('delete', '=', false)
            ->update([
                'delete' => true,
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
            'job:flux',
            'job:flux:action',
            'job:flux:action:PhpmyadminDeploymentDeletion',
        ];
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'flux-phpmyadmin-deployment-deletion';
    }
}
