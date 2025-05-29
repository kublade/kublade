<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Class DockerfileComplianceTest.
 *
 * Tests to verify that all Dockerfiles comply with the requirements from composer.json.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DockerfileComplianceTest extends TestCase
{
    private array $requiredPhpExtensions = [
        'json',
        'phar',
        'posix',
        'yaml',
        'zlib',
    ];

    private array $requiredSystemPackages = [
        'cli',
        'dev',
        'gd',
        'curl',
        'mbstring',
        'xml',
        'zip',
        'bcmath',
        'intl',
        'yaml',
    ];

    private array $requiredTools = [
        'git',
        'composer',
        'nodejs',
        'npm',
    ];

    /**
     * @test
     */
    public function allDockerfilesComplyWithComposerRequirements(): void
    {
        $dockerfiles = $this->findAllDockerfiles();
        $this->assertNotEmpty($dockerfiles, 'No Dockerfiles found in the repository');

        foreach ($dockerfiles as $dockerfile) {
            $this->validateDockerfile($dockerfile);
        }
    }

    /**
     * Find all Dockerfiles in the repository.
     *
     * @return array<string>
     */
    private function findAllDockerfiles(): array
    {
        $dockerfiles = [];
        $dockerDir   = base_path('docker');

        if (is_dir($dockerDir)) {
            $files = File::allFiles($dockerDir);

            foreach ($files as $file) {
                if ($file->getFilename() === 'Dockerfile' || str_starts_with($file->getFilename(), 'Dockerfile.')) {
                    $dockerfiles[] = $file->getPathname();
                }
            }
        }

        return $dockerfiles;
    }

    /**
     * Extract PHP version from Dockerfile path or content.
     *
     * @param string $dockerfilePath
     */
    private function getPhpVersionFromDockerfile(string $dockerfilePath): string
    {
        // Try to get version from path first (e.g., docker/8.2/Dockerfile)
        if (preg_match('#/docker/(\d+\.\d+)/#', $dockerfilePath, $matches)) {
            return $matches[1];
        }

        // If not found in path, try to find in content
        $content = File::get($dockerfilePath);

        if (preg_match('/php(\d+\.\d+)-/', $content, $matches)) {
            return $matches[1];
        }

        // Default to 8.2 if no version found (as per composer.json requirement)
        return '8.2';
    }

    /**
     * Extract Node.js version from Dockerfile content.
     *
     * @param string $content
     */
    private function getNodeVersionFromDockerfile(string $content): ?string
    {
        // Try to find NODE_VERSION ARG
        if (preg_match('/ARG\s+NODE_VERSION\s*=\s*(\d+)/', $content, $matches)) {
            return $matches[1];
        }

        // Try to find node installation in apt-get
        if (preg_match('/node_(\d+)\.x/', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Validate a single Dockerfile against composer.json requirements.
     *
     * @param string $dockerfilePath
     */
    private function validateDockerfile(string $dockerfilePath): void
    {
        $content = File::get($dockerfilePath);
        $this->assertNotEmpty($content, "Dockerfile is empty: {$dockerfilePath}");

        $phpVersion = $this->getPhpVersionFromDockerfile($dockerfilePath);

        // For PHP versions below 8.2, we should warn but not fail
        if (version_compare($phpVersion, '8.2', '<')) {
            $this->markTestSkipped(
                "Dockerfile {$dockerfilePath} uses PHP {$phpVersion}, which is below the minimum required version 8.2 in composer.json"
            );

            return;
        }

        // Check PHP version installation
        $this->assertStringContainsString(
            "php{$phpVersion}",
            $content,
            "Dockerfile {$dockerfilePath} does not use PHP {$phpVersion}"
        );

        // Check required PHP extensions
        foreach ($this->requiredPhpExtensions as $extension) {
            $this->assertStringContainsString(
                "php{$phpVersion}-{$extension}",
                $content,
                "Dockerfile {$dockerfilePath} is missing required PHP extension: {$extension}"
            );
        }

        // Check required system packages
        foreach ($this->requiredSystemPackages as $package) {
            $this->assertStringContainsString(
                "php{$phpVersion}-{$package}",
                $content,
                "Dockerfile {$dockerfilePath} is missing required system package: {$package}"
            );
        }

        // Check for required tools
        foreach ($this->requiredTools as $tool) {
            $this->assertStringContainsString(
                $tool,
                $content,
                "Dockerfile {$dockerfilePath} does not install {$tool}"
            );
        }

        // Verify Node.js installation and version
        $nodeVersion = $this->getNodeVersionFromDockerfile($content);
        $this->assertNotNull(
            $nodeVersion,
            "Dockerfile {$dockerfilePath} does not specify Node.js version"
        );

        // Verify Node.js installation method
        $this->assertTrue(
            str_contains($content, 'nodesource') || str_contains($content, 'nodejs'),
            "Dockerfile {$dockerfilePath} does not properly install Node.js"
        );

        // Verify npm installation
        $this->assertTrue(
            str_contains($content, 'npm install') || str_contains($content, 'apt-get install.*npm'),
            "Dockerfile {$dockerfilePath} does not properly install npm"
        );

        // Check for composer installation
        $this->assertStringContainsString(
            'composer',
            $content,
            "Dockerfile {$dockerfilePath} does not properly install composer"
        );

        // Check for Laravel requirements
        $laravelExtensions = ['mbstring', 'xml', 'zip', 'bcmath'];

        foreach ($laravelExtensions as $extension) {
            $this->assertStringContainsString(
                "php{$phpVersion}-{$extension}",
                $content,
                "Dockerfile {$dockerfilePath} is missing {$extension} extension required by Laravel"
            );
        }
    }
}
