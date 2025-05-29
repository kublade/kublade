<?php

declare(strict_types=1);

namespace App\Helpers\Kubernetes;

use App\Exceptions\HelmException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PharData;
use Symfony\Component\Yaml\Yaml;

/**
 * Class HelmManifests.
 *
 * This class is the helper for the helm manifests.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class HelmManifests
{
    protected static string $repoUrl;

    protected static string $chartName;

    protected static string $repoName;

    protected static string $namespace;

    /**
     * Generate the manifests for the helm chart.
     *
     * @param string $repoUrl
     * @param string $chartName
     * @param string $repoName
     * @param string $namespace
     *
     * @return array
     */
    public static function generateManifests(string $repoUrl, string $chartName, string $repoName = 'helm-repo', string $namespace = 'default'): array
    {
        self::$repoUrl   = $repoUrl;
        self::$chartName = $chartName;
        self::$repoName  = $repoName;
        self::$namespace = $namespace;

        if (self::isOciRepo()) {
            $chartPath = self::pullOciChart();
        } else {
            $index     = self::fetchIndexYaml();
            $chartUrl  = self::getChartDownloadUrl($index);
            $chartPath = self::downloadAndExtractChart($chartUrl);
        }

        $values = self::getChartValues($chartPath);

        return [
            'helmrepository.yaml' => YamlFormatter::format(self::generateHelmRepositoryYaml()),
            'helmrelease.yaml'    => YamlFormatter::format(self::generateHelmReleaseYaml($values)),
            'kustomization.yaml'  => YamlFormatter::format(self::generateKustomizationYaml()),
        ];
    }

    /**
     * Check if the repository is an OCI repository.
     *
     * @return bool
     */
    protected static function isOciRepo(): bool
    {
        return Str::startsWith(self::$repoUrl, 'oci://');
    }

    /**
     * Fetch the index.yaml file from the repository.
     *
     * @return array
     */
    protected static function fetchIndexYaml(): array
    {
        $url      = rtrim(self::$repoUrl, '/') . '/index.yaml';
        $response = Http::get($url);

        if (!$response->successful()) {
            throw new HelmException('Failed to fetch index.yaml');
        }

        return Yaml::parse($response->body());
    }

    /**
     * Get the chart download URL.
     *
     * @param array $index
     *
     * @return string
     */
    protected static function getChartDownloadUrl(array $index): string
    {
        if (!isset($index['entries'][self::$chartName])) {
            $chartName = self::$chartName;

            throw new HelmException('Failed to download chart');
        }

        $latest    = $index['entries'][self::$chartName][0];
        $chartPath = $latest['urls'][0];

        if (
            Str::startsWith($chartPath, 'http') ||
            Str::startsWith($chartPath, 'oci')
        ) {
            return $chartPath;
        }

        return rtrim(self::$repoUrl, '/') . '/' . ltrim($chartPath, '/');
    }

    /**
     * Download and extract the chart.
     *
     * @param string $chartUrl
     *
     * @return string
     */
    protected static function downloadAndExtractChart(string $chartUrl): string
    {
        if (Str::startsWith($chartUrl, 'oci://')) {
            return self::pullOciChartFromFullRef($chartUrl);
        }

        $filename = 'charts/' . Str::uuid();
        $tgzPath  = Storage::path($filename . '.tgz');
        Storage::put($filename . '.tgz', Http::get($chartUrl)->body());

        $phar = new PharData($tgzPath);
        $phar->decompress();
        $tarPath = str_replace('.tgz', '.tar', $tgzPath);

        $pharTar     = new PharData($tarPath);
        $extractPath = Storage::path($filename);
        $pharTar->extractTo($extractPath);

        return $extractPath;
    }

    /**
     * Pull the OCI chart.
     *
     * @return string
     */
    protected static function pullOciChart(): string
    {
        $tmpDir = storage_path('app/charts/' . Str::uuid());

        if (!file_exists($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $ociRef = rtrim(self::$repoUrl, '/') . '/' . self::$chartName;

        $cmd    = ['helm', 'pull', $ociRef, '--untar', '--destination', $tmpDir];
        $result = Process::run($cmd);

        if (!$result->successful()) {
            throw new HelmException('Failed to download chart');
        }

        return $tmpDir;
    }

    /**
     * Pull the OCI chart from a full reference.
     *
     * @param string $ociRef
     *
     * @return string
     */
    protected static function pullOciChartFromFullRef(string $ociRef): string
    {
        $tmpDir = storage_path('app/charts/' . Str::uuid());

        if (!file_exists($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $cmd    = ['helm', 'pull', $ociRef, '--untar', '--destination', $tmpDir];
        $result = Process::run($cmd);

        if (!$result->successful()) {
            throw new HelmException('Failed to download chart');
        }

        return $tmpDir;
    }

    /**
     * Get the chart values.
     *
     * @param string $extractPath
     *
     * @return string
     */
    protected static function getChartValues(string $extractPath): string
    {
        $dirs = scandir($extractPath);

        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            $valuesPath = "$extractPath/$dir/values.yaml";

            if (file_exists($valuesPath)) {
                return file_get_contents($valuesPath);
            }
        }

        throw new HelmException('Failed to fetch values.yaml');
    }

    /**
     * Generate the helm repository yaml.
     *
     * @return string
     */
    protected static function generateHelmRepositoryYaml(): string
    {
        if (self::isOciRepo()) {
            return YamlFormatter::format(
                Yaml::dump([
                    'apiVersion' => 'source.toolkit.fluxcd.io/v1beta2',
                    'kind'       => 'HelmRepository',
                    'metadata'   => [
                        'name' => self::$repoName,
                    ],
                    'spec' => [
                        'type'     => 'oci',
                        'url'      => self::$repoUrl,
                        'interval' => '10m',
                    ],
                ], 10, 2)
            );
        }

        return YamlFormatter::format(
            Yaml::dump([
                'apiVersion' => 'source.toolkit.fluxcd.io/v1beta2',
                'kind'       => 'HelmRepository',
                'metadata'   => [
                    'name' => self::$repoName,
                ],
                'spec' => [
                    'url'      => self::$repoUrl,
                    'interval' => '10m',
                ],
            ], 10, 2)
        );
    }

    /**
     * Generate the helm release yaml.
     *
     * @param string $values
     *
     * @return string
     */
    protected static function generateHelmReleaseYaml(string $values): string
    {
        $base = [
            'apiVersion' => 'helm.toolkit.fluxcd.io/v2beta1',
            'kind'       => 'HelmRelease',
            'metadata'   => [
                'name' => self::$chartName,
            ],
            'spec' => [
                'interval'    => '5m',
                'releaseName' => self::$chartName,
                'chart'       => [
                    'spec' => [
                        'chart'     => self::$chartName,
                        'sourceRef' => [
                            'kind'      => 'HelmRepository',
                            'name'      => self::$repoName,
                            'namespace' => self::$namespace,
                        ],
                        'interval' => '1m',
                    ],
                ],
                'values' => Yaml::parse($values),
            ],
        ];

        return YamlFormatter::format(Yaml::dump($base, 10, 2));
    }

    /**
     * Generate the kustomization yaml.
     *
     * @return string
     */
    protected static function generateKustomizationYaml(): string
    {
        return YamlFormatter::format(
            Yaml::dump([
                'apiVersion' => 'kustomize.config.k8s.io/v1beta1',
                'kind'       => 'Kustomization',
                'namespace'  => self::$namespace,
                'resources'  => [
                    'helmrepository.yaml',
                    'helmrelease.yaml',
                ],
            ], 4, 2)
        );
    }
}
