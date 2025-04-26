<?php

declare(strict_types=1);

namespace App\Helpers\Flux;

use function str_replace;

class Flux
{
    protected const SEALED_SECRET_FILENAME_SUFFIX = '-unsealedsecret.yaml';

    protected const SEALED_SECRET_FILENAME_SUFFIX_TARGET = '-sealedsecret.yaml';

    protected const TEMPLATE_BASE_PATH = 'flux-templates/';

    protected const DEPLOYMENT_BASE_PATH = FluxRepository::DEPLOYMENT_REPOSITORY_PATH . 'customer/';

    protected static function removeTemplateBasePath(array $paths, ?string $template = null)
    {
        return collect($paths)->map(function ($object) use ($template) {
            return str_replace($template ? 'flux-templates/' . $template . '/' : 'flux-templates/', '', $object);
        });
    }
}
