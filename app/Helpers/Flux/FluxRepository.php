<?php

declare(strict_types=1);

namespace App\Helpers\Flux;

use App\Exceptions\FluxException;
use App\Helpers\Git\Git;
use App\Helpers\Git\GitRepo;
use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use Exception;
use Illuminate\Support\Facades\Storage;

/**
 * Class FluxRepository.
 *
 * This class is the helper for the Flux repository.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class FluxRepository
{
    /**
     * The cluster.
     *
     * @var Cluster|null
     */
    public static $cluster;

    /**
     * The repository.
     *
     * @var GitRepo|null
     */
    public static $repository;

    /**
     * Open the Flux repository.
     *
     * @param Cluster $cluster
     */
    public static function open(Cluster $cluster)
    {
        if (!$cluster->gitCredentials) {
            throw new FluxException('Bad Request', 400);
        }

        self::$cluster = $cluster;

        $path        = 'flux-repository/' . $cluster->id;
        $storagePath = Storage::disk('local')->path($path);

        try {
            self::$repository = Git::open($storagePath);
        } catch (Exception $exception) {
            Storage::disk('local')->deleteDirectory($path);

            self::$repository = Git::cloneRemote($storagePath, $cluster->gitCredentials->url);
        }

        self::$repository->run('config user.email "' . $cluster->gitCredentials->email . '"');
        self::$repository->run('config user.name "' . $cluster->gitCredentials->username . '"');
        self::$repository->pull('origin', $cluster->gitCredentials->branch);
    }

    /**
     * Get the Flux repository.
     *
     * @return GitRepo
     */
    public static function get(): GitRepo
    {
        return self::$repository;
    }

    /**
     * Push the Flux repository.
     *
     * @param Deployment $deployment
     * @param string     $type
     */
    public static function push(Deployment $deployment, string $type = 'creation')
    {
        $msg = 'autodeploy(' . $deployment->uuid . "): unknown action\n\nunknown action on application with uuid " . $deployment->uuid . "\n\npart of: no ticket";

        if ($type == 'deletion') {
            $msg = 'autodeploy(' . $deployment->uuid . "): application deployment removed\n\nremoved deployed application with uuid " . $deployment->uuid . "\n\npart of: no ticket";
        } elseif ($type == 'creation') {
            $msg = 'autodeploy(' . $deployment->uuid . "): application deployment created\n\ndeployed new application with uuid " . $deployment->uuid . "\n\npart of: no ticket";
        } elseif ($type == 'update') {
            $msg = 'autodeploy(' . $deployment->uuid . "): application deployment updated\n\nre-deployed existing application with uuid " . $deployment->uuid . "\n\npart of: no ticket";
        }

        self::$repository->commit($msg);
        self::$repository->push('origin', config('flux.git.repository.branch'));

        $log = self::$repository->log();

        preg_match('/(?<=commit )(.*)/', $log, $commits);

        return (object) [
            'msg'  => $msg,
            'hash' => collect($commits ?? [])->unique()->first(),
        ];
    }

    /**
     * Close the Flux repository.
     */
    public static function close()
    {
        self::$cluster    = null;
        self::$repository = null;
    }
}
