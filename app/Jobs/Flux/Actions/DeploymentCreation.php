<?php

declare(strict_types=1);

namespace App\Jobs\Flux\Actions;

use App\Exceptions\FluxException;
use App\Helpers\Flux\FluxDeployment;
use App\Jobs\Base\Job;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentCommit;
use App\Models\Projects\Deployments\DeploymentCommitData;
use App\Models\Projects\Deployments\DeploymentCommitSecretData;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Crypt;

/**
 * Class DeploymentCreation.
 *
 * This class is the action job for processing flux deployment creation.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentCreation extends Job implements ShouldBeUnique
{
    public $deployment_id;

    public static $onQueue = 'flux_deployment';

    /**
     * DeploymentCreation constructor.
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

        $publicData = [];
        $secretData = [];

        $deployment->deploymentData->each(function ($data) use (&$publicData) {
            $publicData[$data->key] = Crypt::decryptString($data->value);
        });

        $deployment->deploymentSecretData->each(function ($data) use (&$secretData) {
            $secretData[$data->key] = Crypt::decryptString($data->value);
        });

        try {
            $release = FluxDeployment::generate($deployment, $publicData, $secretData, false);
        } catch (FluxException $exception) {
            $deployment->ports()->whereNotNull('claim')->delete();

            throw $exception;
        }

        if ($release) {
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
                        'value'                => Crypt::encryptString($value),
                    ]);
                });

                collect($secretData)->each(function ($value, $key) use ($commit) {
                    DeploymentCommitSecretData::create([
                        'deployment_commit_id' => $commit->id,
                        'key'                  => $key,
                        'value'                => Crypt::encryptString($value),
                    ]);
                });
            }

            $deployment->update([
                'deployed_at' => Carbon::now(),
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
            'job:flux:action:DeploymentCreation',
            'job:flux:action:DeploymentCreation:' . $this->deployment_id,
        ];
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'flux-deployment-creation-' . $this->deployment_id;
    }

    /**
     * Set middleware to avoid job overlapping.
     */
    public function middleware()
    {
        return [new WithoutOverlapping('flux_deployment')];
    }
}
