<?php

declare(strict_types=1);

namespace App\Helpers\Kubernetes;

use Illuminate\Support\Str;

/**
 * Class YamlFormatter.
 *
 * This class is the helper for the yaml formatter.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class YamlFormatter
{
    /**
     * Format the yaml.
     *
     * @param string $yaml
     *
     * @return string
     */
    public static function format(string $yaml): string
    {
        if (!Str::startsWith($yaml, '---')) {
            $yaml = "---\n" . $yaml;
        }

        return $yaml;
    }
}
