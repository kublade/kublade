<?php

declare(strict_types=1);

namespace App\Helpers\Flux;

use App\Exceptions\FluxException;
use Illuminate\Support\Facades\Storage;

class FluxDeployment extends Flux
{
    public static function delete(string $uuid, string $template = 'unknown')
    {
        FluxRepository::open();

        $deploymentDirectory = self::DEPLOYMENT_BASE_PATH . $uuid;

        if (!Storage::disk('local')->exists($deploymentDirectory)) {
            throw new FluxException('Not Found', 404);
        }

        if (!Storage::disk('local')->deleteDirectory($deploymentDirectory)) {
            throw new FluxException('Server Error', 500);
        }

        $commit = FluxRepository::push($uuid, 'deletion');
        FluxRepository::close();

        return (object) [
            'template' => $template,
            'uuid'     => $uuid,
            'commit'   => $commit,
        ];
    }
}
