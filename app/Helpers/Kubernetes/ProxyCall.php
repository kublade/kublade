<?php

declare(strict_types=1);

namespace App\Helpers\Kubernetes;

use App\Exceptions\KubeletException;
use Exception;
use Illuminate\Support\Facades\Http;

/**
 * Class ProxyCall.
 *
 * This class is the helper for proxy calls to the kubelet.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ProxyCall
{
    /**
     * Get data from the kubelet.
     *
     * @param string $path
     * @param string $filter
     * @param array  $interfaces
     *
     * @return array
     */
    public static function get(string $path, string $filter = 'container_', $interfaces = ['bond0', 'bond0.4007', 'eth0'])
    {
        if (!config('flux.api.url')) {
            throw new KubeletException('Bad Request', 400);
        }

        try {
            if ($token = config('flux.api.serviceaccount.token')) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])
                    ->withOptions([
                        'verify' => false,
                    ])
                    ->get(config('flux.api.url') . $path)
                    ->body();
            } else {
                $response = Http::withOptions([
                    'verify' => false,
                ])
                    ->get(config('flux.api.url') . $path)
                    ->body();
            }
        } catch (Exception $exception) {
            throw new KubeletException('Server Error', 500);
        }

        if (!$response) {
            throw new KubeletException('Server Error', 500);
        }

        return collect(explode("\n", $response))
            ->filter(function ($line) use ($filter, $interfaces) {
                preg_match('/(?<=interface=")(.[^"]*?)(?="[,}])/', $line, $interface);

                return str_starts_with($line, $filter ?? 'container_') &&
                    collect($interfaces)->filter(function ($filter) use ($interface) {
                        return in_array($filter, $interface);
                    })->isNotEmpty();
            })
            ->map(function ($line) {
                $identifier     = explode('{', $line)[0];
                $explodedString = explode('}', $line);
                $data           = substr(end($explodedString), 1);
                preg_match('/(?<=pod=")(.[^"]*?)(?="[,}])/', $line, $pods);
                preg_match('/(?<=namespace=")(.[^"]*?)(?="[,}])/', $line, $namespaces);
                preg_match('/(?<=interface=")(.[^"]*?)(?="[,}])/', $line, $interfaces);

                return [
                    'identifier' => $identifier,
                    'meta'       => [
                        'namespace' => collect($namespaces)
                            ->filter(function ($value) {
                                return $value !== '';
                            })
                            ->unique()
                            ->first(),
                        'pod' => collect($pods)
                            ->filter(function ($value) {
                                return $value !== '';
                            })
                            ->unique()
                            ->first(),
                        'interface' => collect($interfaces)
                            ->filter(function ($value) {
                                return $value !== '';
                            })
                            ->unique()
                            ->first(),
                    ],
                    'data' => doubleval(explode(' ', $data)[0]),
                ];
            })
            ->filter(function ($dataset) {
                return !(
                    empty($dataset['meta']['namespace']) &&
                    empty($dataset['meta']['pod']) &&
                    empty($dataset['meta']['interface'])
                );
            });
    }
}
