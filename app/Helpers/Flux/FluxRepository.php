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

        $path          = 'flux-repository/' . $cluster->id;
        $storagePath   = Storage::disk('local')->path($path);
        $repositoryUrl = $cluster->gitCredentials->url;

        if (
            str_starts_with($cluster->gitCredentials->url, 'http://') ||
            str_starts_with($cluster->gitCredentials->url, 'https://')
        ) {
            if (!str_contains($cluster->gitCredentials->url, '@')) {
                $repositoryUrl = preg_replace("/^(https?:\/\/)/", '$1' . $cluster->gitCredentials->credentials . '@', $repositoryUrl);
            }
        } else {
            $path = 'clusters/' . $cluster->id . '/git-credentials';

            Storage::disk('local')->put($path, $cluster->gitCredentials->credentials);

            $sshKeyPath = Storage::disk('local')->path($path);
            $sshDir     = '~/.ssh';

            if (!is_dir($sshDir)) {
                mkdir($sshDir, 0700, true);
            }

            $sshConfig        = $sshDir . '/config';
            $sshConfigExisted = true;

            if (!file_exists($sshConfig)) {
                file_put_contents($sshConfig, '');
                $sshConfigExisted = false;
            }

            $sshConfigContent = file_get_contents($sshConfig);

            preg_match('/@(.*?):/', $repositoryUrl, $matches);

            if (count($matches) > 1) {
                if (!str_contains($sshConfigContent, 'Host ' . $cluster->id)) {
                    if ($sshConfigExisted) {
                        $content = $sshConfigContent . <<<EOL

Host $cluster->id
    HostName $matches[1]
    User git
    IdentityFile $sshKeyPath
EOL;
                    } else {
                        $content = <<<EOL
Host $cluster->id
    HostName $matches[1]
    User git
    IdentityFile $sshKeyPath
EOL;
                    }

                    file_put_contents($sshConfig, $content);
                }

                $repositoryUrl = str_replace($matches[1], $cluster->id, $repositoryUrl);
            }
        }

        if (!str_ends_with($repositoryUrl, '.git')) {
            $repositoryUrl .= '.git';
        }

        try {
            self::$repository = Git::open($storagePath);
        } catch (Exception $exception) {
            Storage::disk('local')->deleteDirectory($path);

            self::$repository = Git::cloneRemote($storagePath, $repositoryUrl);
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
        Storage::disk('local')->deleteDirectory(self::$cluster->repositoryPath);

        self::$cluster    = null;
        self::$repository = null;
    }

    /**
     * Clear the cluster repository.
     *
     * @param Cluster $cluster
     */
    public static function clear(Cluster $cluster)
    {
        Storage::disk('local')->deleteDirectory($cluster->repositoryPath);
    }
}
