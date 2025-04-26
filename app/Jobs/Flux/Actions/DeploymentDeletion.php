<?php

declare(strict_types=1);

namespace App\Jobs\Flux\Actions;

use App\Helpers\Flux\FluxDeployment;
use App\Jobs\Base\Job;
use App\Models\Projects\Deployments\Deployment;
use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;

/**
 * Class DeploymentDeletion.
 *
 * This class is the action job for processing flux deployment deletion.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentDeletion extends Job implements ShouldBeUnique
{
    public $tries = 0;

    public $timeout = 3600;

    public $deployment_id;

    public static $onQueue = 'flux_deployment';

    /**
     * DeploymentDeletion constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->deployment_id = $data['deployment_id'];
    }

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        $deployment = Deployment::find($this->deployment_id);

        if (!$deployment) {
            throw new Exception('Deployment not found');
        }

        if ($release = FluxDeployment::delete($deployment->uuid, $deployment->template)) {
            $deployment->commits->each(function ($commit) {
                $commit->commitData()->delete();
                $commit->commitSecretData()->delete();
                $commit->delete();
            });
            $deployment->ports()->delete();
            $deployment->deploymentSecretData()->delete();
            $deployment->deploymentData()->delete();
            $deployment->ftpDeploymentLinks()->each(function ($link) {
                $link->ftpDeployment()
                    ->where('delete', '=', false)
                    ->update([
                        'delete' => true,
                    ]);
                $link->delete();
            });
            $deployment->phpmyadminDeploymentLinks()->each(function ($link) {
                $link->phpmyadminDeployment()
                    ->where('delete', '=', false)
                    ->update([
                        'delete' => true,
                    ]);
                $link->delete();
            });
            $deployment->metrics()->delete();
            $deployment->delete();
        }
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
            'job:flux:action:DeploymentDeletion',
            'job:flux:action:DeploymentDeletion:' . $this->deployment_id,
        ];
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'flux-deployment-deletion-' . $this->deployment_id;
    }

    /**
     * Set middleware to avoid job overlapping.
     */
    public function middleware()
    {
        return [new WithoutOverlapping('flux_deployment')];
    }
}
