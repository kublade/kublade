<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Class CheckVersion.
 *
 * This class is the middleware for checking the version.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class CheckVersion
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $newestRelease = Cache::get('kublade_newest_release');

            if (! $newestRelease) {
                $newestRelease = Http::get('https://api.github.com/repos/kublade/kublade/releases/latest')->json();

                Cache::put('kublade_newest_release', $newestRelease, 60 * 60 * 24);
            }

            if (! Str::startsWith($newestRelease['tag_name'], 'v')) {
                $newestRelease['tag_name'] = 'v' . $newestRelease['tag_name'];
            }

            $composerContent = json_decode(file_get_contents(base_path('composer.json')), true);

            if (! Str::startsWith($composerContent['version'], 'v')) {
                $composerContent['version'] = 'v' . $composerContent['version'];
            }

            $request->attributes->add([
                'version' => $composerContent['version'],
            ]);

            if ($newestRelease['tag_name'] > $composerContent['version']) {
                $request->attributes->add([
                    'update' => $newestRelease,
                ]);
            }
        } catch (Exception $e) {
        }

        return $next($request);
    }
}
