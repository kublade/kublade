<?php

declare(strict_types=1);

namespace App\Helpers\Flux;

use App\Helpers\Git\Git;
use Exception;
use Illuminate\Support\Facades\Storage;

class FluxRepository
{
    public const DEPLOYMENT_REPOSITORY_PATH = 'flux-repository/';

    public static $fluxRepository;

    public static function open()
    {
        $storagePath = Storage::disk('local')->path('');

        try {
            self::$fluxRepository = Git::open($storagePath . self::DEPLOYMENT_REPOSITORY_PATH);
        } catch (Exception $exception) {
            Storage::disk('local')->deleteDirectory(self::DEPLOYMENT_REPOSITORY_PATH);

            $fluxRepositoryRemote = config('flux.git.repository.remote');
            self::$fluxRepository = Git::cloneRemote($storagePath . self::DEPLOYMENT_REPOSITORY_PATH, $fluxRepositoryRemote);
        }

        self::$fluxRepository->run('config user.email "' . config('flux.git.user.email') . '"');
        self::$fluxRepository->run('config user.name "' . config('flux.git.user.name') . '"');
        self::$fluxRepository->pull('origin', config('flux.git.repository.branch'));
    }

    public static function get()
    {
        return self::$fluxRepository;
    }

    public static function push(string $uuid, string $type = 'creation')
    {
        self::$fluxRepository->add('.');

        $msg = 'autodeploy(' . $uuid . "): unknown action\n\nunknown action on application with uuid " . $uuid . "\n\npart of: no ticket";

        if ($type == 'deletion') {
            $msg = 'autodeploy(' . $uuid . "): application deployment removed\n\nremoved deployed application with uuid " . $uuid . "\n\npart of: no ticket";
        } elseif ($type == 'creation') {
            $msg = 'autodeploy(' . $uuid . "): application deployment created\n\ndeployed new application with uuid " . $uuid . "\n\npart of: no ticket";
        } elseif ($type == 'update') {
            $msg = 'autodeploy(' . $uuid . "): application deployment updated\n\nre-deployed existing application with uuid " . $uuid . "\n\npart of: no ticket";
        }

        self::$fluxRepository->commit($msg);
        self::$fluxRepository->push('origin', config('flux.git.repository.branch'));

        $log = self::$fluxRepository->log();

        preg_match('/(?<=commit )(.*)/', $log, $commits);

        return (object) [
            'msg'  => $msg,
            'hash' => collect($commits ?? [])->unique()->first(),
        ];
    }

    public static function close()
    {
        self::$fluxRepository = null;
    }
}
