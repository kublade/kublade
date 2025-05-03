<?php

declare(strict_types=1);

namespace App\Jobs\Flux\Actions;

use App\Helpers\Flux\FluxDeployment;
use App\Jobs\Base\Job;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentCommit;
use App\Models\Projects\Deployments\DeploymentCommitData;
use App\Models\Projects\Deployments\DeploymentCommitSecretData;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;

/**
 * Class DeploymentUpdate.
 *
 * This class is the action job for processing flux deployment updates.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentUpdate extends Job implements ShouldBeUnique
{
    public $deployment_id;

    public static $onQueue = 'flux_deployment';

    /**
     * DeploymentUpdate constructor.
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
            return;
        }

        $publicData = [];
        $secretData = [];

        $deployment->deploymentData->each(function ($data) use (&$publicData) {
            $publicData[$data->key] = $data->value;
        });

        $deployment->deploymentSecretData->each(function ($data) use (&$secretData) {
            $secretData[$data->key] = $data->value;
        });

        if ($release = FluxDeployment::generate($deployment, $publicData, $secretData, true)) {
            if (
                $commit = DeploymentCommit::create([
                    'deployment_id' => $deployment->id,
                    'hash'          => $release->hash,
                    'message'       => $release->msg,
                ])
            ) {
                collect($publicData)->each(function ($value, $key) use ($commit) {
                    DeploymentCommitData::create([
                        'deployment_commit_id' => $commit->id,
                        'key'                  => $key,
                        'value'                => $value,
                    ]);
                });

                collect($secretData)->each(function ($value, $key) use ($commit) {
                    DeploymentCommitSecretData::create([
                        'deployment_commit_id' => $commit->id,
                        'key'                  => $key,
                        'value'                => $value,
                    ]);
                });
            }

            $deployment->update([
                'deployment_updated_at' => Carbon::now(),
            ]);
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
            'job:flux:action:DeploymentUpdate',
            'job:flux:action:DeploymentUpdate:' . $this->deployment_id,
        ];
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'flux-deployment-update-' . $this->deployment_id;
    }

    /**
     * Set middleware to avoid job overlapping.
     */
    public function middleware()
    {
        return [new WithoutOverlapping('flux_deployment')];
    }
}
