<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers\Kubernetes;

use App\Exceptions\HelmException;
use App\Helpers\Kubernetes\HelmManifests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Phar;
use PharData;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

/**
 * Class HelmManifestsTest.
 *
 * Unit tests for the HelmManifests helper class.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class HelmManifestsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    /**
     * @test
     *
     * @group helm-manifests
     */
    public function itCanGenerateManifestsForRegularHelmRepo(): void
    {
        // Mock HTTP response for index.yaml
        Http::fake([
            'https://charts.example.com/index.yaml' => Http::response([
                'entries' => [
                    'test-chart' => [
                        [
                            'urls'    => ['test-chart-1.0.0.tgz'],
                            'version' => '1.0.0',
                        ],
                    ],
                ],
            ]),
            'https://charts.example.com/test-chart-1.0.0.tgz' => Http::response('fake-chart-content'),
        ]);

        // Mock Process for helm pull
        Process::fake([
            'helm pull *' => Process::result(0, '', 0),
        ]);

        // Create a mock directory structure in a temporary location
        $tempDir = storage_path('framework/testing/disks/local/temp/' . uniqid());
        mkdir($tempDir, 0755, true);
        mkdir($tempDir . '/test-chart', 0755, true);
        file_put_contents($tempDir . '/test-chart/values.yaml', "replicaCount: 1\nimage: test:latest");

        // Create a valid tar.gz file
        $chartDir = storage_path('framework/testing/disks/local/charts/' . uniqid());
        $this->createValidTarGz($tempDir, $chartDir);

        // Clean up temp directory
        $this->deleteDirectory($tempDir);

        // Mock storage operations
        Storage::shouldReceive('put')
            ->once()
            ->andReturn(true);

        Storage::shouldReceive('path')
            ->andReturnUsing(function ($path) use ($chartDir) {
                if (str_ends_with($path, '.tgz')) {
                    return $chartDir . '.tgz';
                }

                if (str_ends_with($path, '.tar')) {
                    return $chartDir . '.tar';
                }

                return $chartDir;
            });

        $manifests = HelmManifests::generateManifests(
            'https://charts.example.com',
            'test-chart',
            'test-repo',
            'test-namespace'
        );

        $this->assertArrayHasKey('helmrepository.yaml', $manifests);
        $this->assertArrayHasKey('helmrelease.yaml', $manifests);
        $this->assertArrayHasKey('kustomization.yaml', $manifests);

        // Verify HelmRepository YAML
        $repoYaml = Yaml::parse($manifests['helmrepository.yaml']);
        $this->assertEquals('source.toolkit.fluxcd.io/v1beta2', $repoYaml['apiVersion']);
        $this->assertEquals('HelmRepository', $repoYaml['kind']);
        $this->assertEquals('test-repo', $repoYaml['metadata']['name']);
        $this->assertEquals('https://charts.example.com', $repoYaml['spec']['url']);

        // Verify HelmRelease YAML
        $releaseYaml = Yaml::parse($manifests['helmrelease.yaml']);
        $this->assertEquals('helm.toolkit.fluxcd.io/v2beta1', $releaseYaml['apiVersion']);
        $this->assertEquals('HelmRelease', $releaseYaml['kind']);
        $this->assertEquals('test-chart', $releaseYaml['metadata']['name']);
        $this->assertEquals('test-chart', $releaseYaml['spec']['chart']['spec']['chart']);
    }

    /**
     * @test
     *
     * @group helm-manifests
     */
    public function itCanGenerateManifestsForOciRepo(): void
    {
        // Create a base directory for our test
        $baseDir = storage_path('app/charts');

        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        // Create a class to hold our directory reference
        $dirHolder = new class () {
            public $actualChartDir = null;
        };

        // Create a custom Process mock
        $processMock = new class ($dirHolder) {
            private $dirHolder;

            public function __construct($dirHolder)
            {
                $this->dirHolder = $dirHolder;
            }

            public function run($command)
            {
                // Check if this is a helm pull command
                if (is_array($command) &&
                    $command[0] === 'helm' &&
                    $command[1] === 'pull' &&
                    str_starts_with($command[2], 'oci://') &&
                    $command[3] === '--untar' &&
                    $command[4] === '--destination') {

                    // Store the actual directory path
                    $this->dirHolder->actualChartDir = $command[5];

                    // Create the directory structure synchronously
                    $this->createDirectoryStructure($this->dirHolder->actualChartDir);

                    // Return success
                    return new class () {
                        public function successful()
                        {
                            return true;
                        }
                    };
                }

                // For any other command, return failure
                return new class () {
                    public function successful()
                    {
                        return false;
                    }
                };
            }

            private function createDirectoryStructure(string $baseDir): void
            {
                // Create base directory if it doesn't exist
                if (!is_dir($baseDir)) {
                    mkdir($baseDir, 0755, true);
                }

                // Create test-chart directory
                $testChartDir = $baseDir . '/test-chart';

                if (!is_dir($testChartDir)) {
                    mkdir($testChartDir, 0755, true);
                }

                // Create values.yaml
                $valuesPath    = $testChartDir . '/values.yaml';
                $valuesContent = "replicaCount: 1\nimage: test:latest";
                file_put_contents($valuesPath, $valuesContent);

                // List all files in the directories
                $baseFiles  = scandir($baseDir);
                $chartFiles = scandir($testChartDir);

                // Ensure files are written to disk
                clearstatcache(true, $baseDir);
                clearstatcache(true, $testChartDir);
                clearstatcache(true, $valuesPath);

                // Double-check the values.yaml content
                if (file_exists($valuesPath)) {
                    $content = file_get_contents($valuesPath);
                }
            }
        };

        // Bind our custom Process mock to the container
        $this->app->instance('process', $processMock);

        // Also bind it to the Process facade
        Process::swap($processMock);

        // Mock storage operations to return the actual path
        Storage::shouldReceive('path')
            ->andReturnUsing(function ($path) use ($dirHolder) {
                if (str_contains($path, 'charts/')) {
                    if ($dirHolder->actualChartDir === null) {
                        return storage_path('app/' . $path);
                    }

                    return $dirHolder->actualChartDir;
                }
                $appPath = storage_path('app/' . $path);

                return $appPath;
            });

        // Don't mock file operations, use the actual filesystem
        $manifests = HelmManifests::generateManifests(
            'oci://registry.example.com',
            'test-chart',
            'test-repo',
            'test-namespace'
        );

        // Verify OCI HelmRepository YAML
        $repoYaml = Yaml::parse($manifests['helmrepository.yaml']);
        $this->assertEquals('oci', $repoYaml['spec']['type']);
        $this->assertEquals('oci://registry.example.com', $repoYaml['spec']['url']);

        // Verify HelmRelease YAML
        $releaseYaml = Yaml::parse($manifests['helmrelease.yaml']);
        $this->assertEquals('helm.toolkit.fluxcd.io/v2beta1', $releaseYaml['apiVersion']);
        $this->assertEquals('HelmRelease', $releaseYaml['kind']);
        $this->assertEquals('test-chart', $releaseYaml['metadata']['name']);
        $this->assertEquals('test-chart', $releaseYaml['spec']['chart']['spec']['chart']);

        // Clean up
        if ($dirHolder->actualChartDir !== null) {
            $this->deleteDirectory($dirHolder->actualChartDir);
        }
    }

    /**
     * @test
     *
     * @group helm-manifests
     */
    public function itThrowsExceptionWhenIndexYamlFetchFails(): void
    {
        Http::fake([
            'https://charts.example.com/index.yaml' => Http::response('', 404),
        ]);

        $this->expectException(HelmException::class);
        $this->expectExceptionMessage('Failed to fetch index.yaml');

        HelmManifests::generateManifests(
            'https://charts.example.com',
            'test-chart'
        );
    }

    /**
     * @test
     *
     * @group helm-manifests
     */
    public function itThrowsExceptionWhenChartNotFoundInIndex(): void
    {
        Http::fake([
            'https://charts.example.com/index.yaml' => Http::response([
                'entries' => [],
            ]),
        ]);

        $this->expectException(HelmException::class);
        $this->expectExceptionMessage('Failed to download chart');

        HelmManifests::generateManifests(
            'https://charts.example.com',
            'non-existent-chart'
        );
    }

    /**
     * @test
     *
     * @group helm-manifests
     */
    public function itThrowsExceptionWhenHelmPullFails(): void
    {
        Process::fake([
            'helm pull *' => Process::result(1, 'Error pulling chart', 1),
        ]);

        $this->expectException(HelmException::class);
        $this->expectExceptionMessage('Failed to download chart');

        HelmManifests::generateManifests(
            'oci://registry.example.com',
            'test-chart'
        );
    }

    /**
     * @test
     *
     * @group helm-manifests
     */
    public function itHandlesAbsoluteChartUrls(): void
    {
        Http::fake([
            'https://charts.example.com/index.yaml' => Http::response([
                'entries' => [
                    'test-chart' => [
                        [
                            'urls'    => ['https://other-registry.com/charts/test-chart-1.0.0.tgz'],
                            'version' => '1.0.0',
                        ],
                    ],
                ],
            ]),
            'https://other-registry.com/charts/test-chart-1.0.0.tgz' => Http::response('fake-chart-content'),
        ]);

        Process::fake([
            'helm pull *' => Process::result(0, '', 0),
        ]);

        // Create a mock directory structure in a temporary location
        $tempDir = storage_path('framework/testing/disks/local/temp/' . uniqid());
        mkdir($tempDir, 0755, true);
        mkdir($tempDir . '/test-chart', 0755, true);
        file_put_contents($tempDir . '/test-chart/values.yaml', "replicaCount: 1\nimage: test:latest");

        // Create a valid tar.gz file
        $chartDir = storage_path('framework/testing/disks/local/charts/' . uniqid());
        $this->createValidTarGz($tempDir, $chartDir);

        // Clean up temp directory
        $this->deleteDirectory($tempDir);

        // Mock storage operations
        Storage::shouldReceive('put')
            ->once()
            ->andReturn(true);

        Storage::shouldReceive('path')
            ->andReturnUsing(function ($path) use ($chartDir) {
                if (str_ends_with($path, '.tgz')) {
                    return $chartDir . '.tgz';
                }

                if (str_ends_with($path, '.tar')) {
                    return $chartDir . '.tar';
                }

                return $chartDir;
            });

        $manifests = HelmManifests::generateManifests(
            'https://charts.example.com',
            'test-chart'
        );

        $this->assertArrayHasKey('helmrepository.yaml', $manifests);
        $this->assertArrayHasKey('helmrelease.yaml', $manifests);
    }

    /**
     * @test
     *
     * @group helm-manifests
     */
    public function itGeneratesCorrectKustomizationYaml(): void
    {
        Http::fake([
            'https://charts.example.com/index.yaml' => Http::response([
                'entries' => [
                    'test-chart' => [
                        [
                            'urls'    => ['test-chart-1.0.0.tgz'],
                            'version' => '1.0.0',
                        ],
                    ],
                ],
            ]),
            'https://charts.example.com/test-chart-1.0.0.tgz' => Http::response('fake-chart-content'),
        ]);

        Process::fake([
            'helm pull *' => Process::result(0, '', 0),
        ]);

        // Create a mock directory structure in a temporary location
        $tempDir = storage_path('framework/testing/disks/local/temp/' . uniqid());
        mkdir($tempDir, 0755, true);
        mkdir($tempDir . '/test-chart', 0755, true);
        file_put_contents($tempDir . '/test-chart/values.yaml', "replicaCount: 1\nimage: test:latest");

        // Create a valid tar.gz file
        $chartDir = storage_path('framework/testing/disks/local/charts/' . uniqid());
        $this->createValidTarGz($tempDir, $chartDir);

        // Clean up temp directory
        $this->deleteDirectory($tempDir);

        // Mock storage operations
        Storage::shouldReceive('put')
            ->once()
            ->andReturn(true);

        Storage::shouldReceive('path')
            ->andReturnUsing(function ($path) use ($chartDir) {
                if (str_ends_with($path, '.tgz')) {
                    return $chartDir . '.tgz';
                }

                if (str_ends_with($path, '.tar')) {
                    return $chartDir . '.tar';
                }

                return $chartDir;
            });

        $manifests = HelmManifests::generateManifests(
            'https://charts.example.com',
            'test-chart',
            'test-repo',
            'test-namespace'
        );

        $kustomizationYaml = Yaml::parse($manifests['kustomization.yaml']);
        $this->assertEquals('kustomize.config.k8s.io/v1beta1', $kustomizationYaml['apiVersion']);
        $this->assertEquals('Kustomization', $kustomizationYaml['kind']);
        $this->assertEquals('test-namespace', $kustomizationYaml['namespace']);
        $this->assertEquals(['helmrepository.yaml', 'helmrelease.yaml'], $kustomizationYaml['resources']);
    }

    protected function tearDown(): void
    {
        // Clean up test directories
        $chartsDir = storage_path('framework/testing/disks/local/charts');

        if (is_dir($chartsDir)) {
            $this->deleteDirectory($chartsDir);
        }
        $tempDir = storage_path('framework/testing/disks/local/temp');

        if (is_dir($tempDir)) {
            $this->deleteDirectory($tempDir);
        }
        parent::tearDown();
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }

    private function createValidTarGz(string $sourceDir, string $targetDir): void
    {
        // Create a temporary directory for our operations
        $tempDir = storage_path('framework/testing/disks/local/temp/' . uniqid());

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Create a tar file in the temp directory with .tar extension
        $tempTarFile = $tempDir . '/archive.tar';
        $phar        = new PharData($tempTarFile);
        $phar->buildFromDirectory($sourceDir);

        // Create a gzipped tar file
        $phar->compress(Phar::GZ);

        // Ensure target directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Move the .tar.gz to the target location with .tgz extension
        rename($tempTarFile . '.gz', $targetDir . '.tgz');

        // Clean up temporary directory
        $this->deleteDirectory($tempDir);
    }
}
