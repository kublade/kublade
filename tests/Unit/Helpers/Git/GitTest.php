<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers\Git;

use App\Helpers\Git\Git;
use App\Helpers\Git\GitRepo;
use Exception;
use stdClass;
use Tests\TestCase;

/**
 * Class GitTest.
 *
 * Unit tests for the Git helper class.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class GitTest extends TestCase
{
    private string $testRepoPath;

    private string $originalBin;

    private array $originalGitConfig;

    private const TEST_GIT_USER = 'Test User';

    private const TEST_GIT_EMAIL = 'test@example.com';

    private const DEFAULT_BRANCH = 'main';

    protected function setUp(): void
    {
        parent::setUp();

        // Store original git binary path
        $this->originalBin = Git::getBin();

        // Store original git config
        $this->originalGitConfig = [
            'user.name'          => $this->getGitConfig('user.name'),
            'user.email'         => $this->getGitConfig('user.email'),
            'init.defaultBranch' => $this->getGitConfig('init.defaultBranch'),
        ];

        // Always set test git config globally
        $this->setGitConfig('user.name', self::TEST_GIT_USER, true);
        $this->setGitConfig('user.email', self::TEST_GIT_EMAIL, true);
        $this->setGitConfig('init.defaultBranch', self::DEFAULT_BRANCH, true);

        // Create a temporary directory for test repositories
        $this->testRepoPath = storage_path('framework/testing/git-test-' . uniqid());

        if (!is_dir($this->testRepoPath)) {
            mkdir($this->testRepoPath, 0755, true);
        }

        // Initialize the test directory as a git repository
        $this->initializeGitRepository($this->testRepoPath);
    }

    /**
     * Initialize a directory as a git repository with proper config.
     *
     * @param string $path
     */
    private function initializeGitRepository(string $path): void
    {
        // Initialize git repository with -q to suppress hints
        $command = sprintf('cd %s && %s init -q', escapeshellarg($path), Git::getBin());
        exec($command);

        // Set local git config
        $this->setGitConfig('user.name', self::TEST_GIT_USER, false, $path);
        $this->setGitConfig('user.email', self::TEST_GIT_EMAIL, false, $path);
    }

    protected function tearDown(): void
    {
        // Restore original git binary path
        Git::setBin($this->originalBin);

        // Restore original git config
        foreach ($this->originalGitConfig as $key => $value) {
            if ($value === null) {
                $this->unsetGitConfig($key, true);
            } else {
                $this->setGitConfig($key, $value, true);
            }
        }

        // Clean up test directory
        if (is_dir($this->testRepoPath)) {
            $this->deleteDirectory($this->testRepoPath);
        }

        parent::tearDown();
    }

    /**
     * Get git config value.
     *
     * @param string      $key
     * @param bool        $global Whether to use global config
     * @param string|null $path   Path to repository for local config
     */
    private function getGitConfig(string $key, bool $global = true, ?string $path = null): ?string
    {
        $command = sprintf(
            '%s config %s --get %s',
            Git::getBin(),
            $global ? '--global' : '',
            escapeshellarg($key)
        );

        if ($path !== null) {
            $command = sprintf('cd %s && %s', escapeshellarg($path), $command);
        }

        exec($command, $output, $returnCode);

        return $returnCode === 0 ? $output[0] : null;
    }

    /**
     * Set git config value.
     *
     * @param string      $key
     * @param string      $value
     * @param bool        $global Whether to use global config
     * @param string|null $path   Path to repository for local config
     */
    private function setGitConfig(string $key, string $value, bool $global = true, ?string $path = null): void
    {
        $command = sprintf(
            '%s config %s %s %s',
            Git::getBin(),
            $global ? '--global' : '',
            escapeshellarg($key),
            escapeshellarg($value)
        );

        if ($path !== null) {
            $command = sprintf('cd %s && %s', escapeshellarg($path), $command);
        }

        exec($command);
    }

    /**
     * Unset git config value.
     *
     * @param string      $key
     * @param bool        $global Whether to use global config
     * @param string|null $path   Path to repository for local config
     */
    private function unsetGitConfig(string $key, bool $global = true, ?string $path = null): void
    {
        $command = sprintf(
            '%s config %s --unset %s',
            Git::getBin(),
            $global ? '--global' : '',
            escapeshellarg($key)
        );

        if ($path !== null) {
            $command = sprintf('cd %s && %s', escapeshellarg($path), $command);
        }

        exec($command);
    }

    /**
     * @test
     */
    public function itCanGetAndSetGitBinaryPath(): void
    {
        // Test default path
        $this->assertEquals('/usr/bin/git', Git::getBin());

        // Test setting custom path
        $customPath = '/custom/path/to/git';
        Git::setBin($customPath);
        $this->assertEquals($customPath, Git::getBin());

        // Test setting back to default
        Git::setBin('/usr/bin/git');
        $this->assertEquals('/usr/bin/git', Git::getBin());
    }

    /**
     * @test
     */
    public function itCanSwitchToWindowsMode(): void
    {
        Git::windowsMode();
        $this->assertEquals('git', Git::getBin());
    }

    /**
     * @test
     */
    public function itCanCreateNewRepository(): void
    {
        $repoPath = $this->testRepoPath . '/new-repo';

        // Create new repository
        $repo = Git::create($repoPath);

        $this->assertInstanceOf(GitRepo::class, $repo);
        $this->assertDirectoryExists($repoPath);
        $this->assertDirectoryExists($repoPath . '/.git');
    }

    /**
     * @test
     */
    public function itCanCreateNewRepositoryWithSource(): void
    {
        // Create source directory with some files
        $sourcePath = $this->testRepoPath . '/source';
        mkdir($sourcePath, 0755, true);
        file_put_contents($sourcePath . '/test.txt', 'Test content');

        // Initialize source as a git repository
        $this->initializeGitRepository($sourcePath);

        // Add and commit the test file
        $sourceRepo = Git::open($sourcePath);
        $sourceRepo->add('.');
        $sourceRepo->commit('Initial commit');

        // Create target directory (but don't initialize it as a git repository)
        $repoPath = $this->testRepoPath . '/new-repo-with-source';
        mkdir($repoPath, 0755, true);

        // Create new repository with source
        $repo = Git::create($repoPath, $sourcePath);

        $this->assertInstanceOf(GitRepo::class, $repo);
        $this->assertDirectoryExists($repoPath);
        $this->assertDirectoryExists($repoPath . '/.git');
        $this->assertFileExists($repoPath . '/test.txt');
        $this->assertEquals('Test content', file_get_contents($repoPath . '/test.txt'));
    }

    /**
     * @test
     */
    public function itCanOpenExistingRepository(): void
    {
        // Create a repository first
        $repoPath = $this->testRepoPath . '/existing-repo';
        Git::create($repoPath);

        // Open the repository
        $repo = Git::open($repoPath);

        $this->assertInstanceOf(GitRepo::class, $repo);
    }

    /**
     * @test
     */
    public function itThrowsExceptionWhenOpeningNonExistentRepository(): void
    {
        $this->expectException(Exception::class);

        Git::open($this->testRepoPath . '/non-existent-repo');
    }

    /**
     * @test
     */
    public function itCanCloneRemoteRepository(): void
    {
        // This test requires a real git repository to clone
        // Using a small test repository
        $repoPath = $this->testRepoPath . '/cloned-repo';
        $remote   = 'https://github.com/octocat/Hello-World.git';

        $repo = Git::cloneRemote($repoPath, $remote);

        $this->assertInstanceOf(GitRepo::class, $repo);
        $this->assertDirectoryExists($repoPath);
        $this->assertDirectoryExists($repoPath . '/.git');
    }

    /**
     * @test
     */
    public function itCanCloneRemoteRepositoryWithReference(): void
    {
        // First clone the reference repository
        $referencePath = $this->testRepoPath . '/reference-repo';
        $remote        = 'https://github.com/octocat/Hello-World.git';
        Git::cloneRemote($referencePath, $remote);

        // Now clone using the reference
        $repoPath = $this->testRepoPath . '/cloned-repo-with-reference';
        $repo     = Git::cloneRemote($repoPath, $remote, $referencePath);

        $this->assertInstanceOf(GitRepo::class, $repo);
        $this->assertDirectoryExists($repoPath);
        $this->assertDirectoryExists($repoPath . '/.git');
    }

    /**
     * @test
     */
    public function itCanDetectGitRepoInstances(): void
    {
        $repoPath = $this->testRepoPath . '/test-repo';
        $repo     = Git::create($repoPath);

        $this->assertTrue(Git::isRepo($repo));
        $this->assertFalse(Git::isRepo(new stdClass()));
        $this->assertFalse(Git::isRepo('not a repo'));
        $this->assertFalse(Git::isRepo(null));
        $this->assertFalse(Git::isRepo(123));
        $this->assertFalse(Git::isRepo([]));
    }

    /**
     * @test
     */
    public function itThrowsExceptionWhenCreatingRepositoryInNonExistentDirectory(): void
    {
        $this->expectException(Exception::class);

        Git::create($this->testRepoPath . '/non-existent/path/repo');
    }

    /**
     * @test
     */
    public function itThrowsExceptionWhenCreatingRepositoryInNonWritableDirectory(): void
    {
        // Create a directory that we'll make non-writable
        $nonWritablePath = $this->testRepoPath . '/non-writable';
        mkdir($nonWritablePath, 0755, true);

        // Remove write permissions
        chmod($nonWritablePath, 0555);

        // Verify the directory is actually non-writable
        if (is_writable($nonWritablePath)) {
            $this->markTestSkipped('Could not make directory non-writable. Current permissions: ' . substr(sprintf('%o', fileperms($nonWritablePath)), -4));
        }

        $repoPath = $nonWritablePath . '/test-repo';

        try {
            // Try to create a repository in the non-writable directory
            Git::create($repoPath);

            // If we get here, the repository was created successfully
            // This shouldn't happen, so let's check if the directory is actually writable
            if (is_writable($nonWritablePath)) {
                $this->fail(sprintf(
                    'Directory is still writable despite chmod. Current permissions: %s',
                    substr(sprintf('%o', fileperms($nonWritablePath)), -4)
                ));
            } else {
                $this->fail('Repository was created in a non-writable directory');
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            // Check for common permission-related error messages
            $hasPermissionError = str_contains($errorMessage, 'Permission denied') ||
                str_contains($errorMessage, 'cannot create') ||
                str_contains($errorMessage, 'access denied') ||
                str_contains($errorMessage, 'not a directory') ||
                str_contains($errorMessage, 'failed to mkdir') ||
                str_contains($errorMessage, 'cannot open') ||
                str_contains($errorMessage, 'cannot write') ||
                str_contains($errorMessage, 'read-only') ||
                str_contains($errorMessage, 'operation not permitted');

            $this->assertTrue(
                $hasPermissionError,
                sprintf(
                    'Exception message "%s" does not contain any expected permission error text. Directory permissions: %s',
                    $errorMessage,
                    substr(sprintf('%o', fileperms($nonWritablePath)), -4)
                )
            );
        } finally {
            // Restore permissions so we can clean up
            chmod($nonWritablePath, 0755);

            // Clean up the repository if it was created
            if (is_dir($repoPath)) {
                $this->deleteDirectory($repoPath);
            }
        }
    }

    /**
     * @test
     */
    public function itThrowsExceptionWhenCloningToNonExistentDirectory(): void
    {
        $this->expectException(Exception::class);

        Git::cloneRemote(
            $this->testRepoPath . '/non-existent/path/repo',
            'https://github.com/octocat/Hello-World.git'
        );
    }

    /**
     * @test
     */
    public function itThrowsExceptionWhenCloningWithInvalidRemote(): void
    {
        $this->expectException(Exception::class);

        Git::cloneRemote(
            $this->testRepoPath . '/invalid-repo',
            'https://github.com/non-existent-repo.git'
        );
    }

    /**
     * @test
     */
    public function itCanPerformComplexGitOperations(): void
    {
        // Create a new repository
        $repoPath = $this->testRepoPath . '/complex-repo';
        $repo     = Git::create($repoPath);

        // Ensure Git user identity is set for this repository
        // This is especially important in CI environments like GitHub Actions
        $this->setGitConfig('user.name', self::TEST_GIT_USER, false, $repoPath);
        $this->setGitConfig('user.email', self::TEST_GIT_EMAIL, false, $repoPath);

        // Test initial state
        // In a fresh repository, there are no branches until the first commit
        $this->assertNull($repo->activeBranch());
        $this->assertEmpty($repo->listBranches());
        $this->assertEmpty($repo->listTags());

        // Create and commit initial file
        file_put_contents($repoPath . '/test.txt', 'Initial content');
        $repo->add('.');
        $repo->commit('Initial commit');

        // After first commit, we should have a default branch
        // Note: Different Git versions might use 'main' or 'master' as default
        $defaultBranch = $repo->activeBranch();
        $this->assertContains($defaultBranch, ['main', 'master'], 'Default branch should be either main or master');

        // Test branch operations
        $repo->createBranch('feature-branch');
        $this->assertContains('feature-branch', $repo->listBranches());

        $repo->checkout('feature-branch');
        $this->assertEquals('feature-branch', $repo->activeBranch());

        // Make changes in feature branch
        file_put_contents($repoPath . '/feature.txt', 'Feature content');
        $repo->add('.');
        $repo->commit('Add feature file');

        // Test tag operations
        $repo->addTag('v1.0.0', 'First release');
        $this->assertContains('v1.0.0', $repo->listTags());

        // Test merge operations
        $repo->checkout($defaultBranch);
        $this->assertEquals($defaultBranch, $repo->activeBranch());

        // Create a conflict
        file_put_contents($repoPath . '/test.txt', 'Main branch content');
        $repo->add('.');
        $repo->commit('Update in main branch');

        // Try to merge feature branch
        try {
            $repo->merge('feature-branch');
            $this->fail('Expected merge conflict was not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString('conflict', strtolower($e->getMessage()));
        }

        // Resolve conflict
        file_put_contents($repoPath . '/test.txt', 'Merged content');
        $repo->add('.');
        $repo->commit('Merge feature branch');

        // Test log operations
        $log = $repo->log();
        $this->assertStringContainsString('Merge feature branch', $log);
        $this->assertStringContainsString('Update in main branch', $log);
        $this->assertStringContainsString('Add feature file', $log);
        $this->assertStringContainsString('Initial commit', $log);

        // Test custom log format
        $logFormat    = '%h|%s|%an|%ad';
        $formattedLog = $repo->log($logFormat);
        $this->assertStringContainsString('|Merge feature branch|', $formattedLog);

        // Test file operations
        $repo->rm('feature.txt');
        $this->assertFileDoesNotExist($repoPath . '/feature.txt');

        $repo->commit('Remove feature file');
        $this->assertStringContainsString('Remove feature file', $repo->log());

        // Test clean operation
        file_put_contents($repoPath . '/untracked.txt', 'Untracked content');
        $repo->clean(true, true);
        $this->assertFileDoesNotExist($repoPath . '/untracked.txt');

        // Test description
        $description = 'Test repository for complex operations';
        $repo->setDescription($description);
        $this->assertEquals($description, $repo->getDescription());

        // Test status
        $status = $repo->status();
        $this->assertStringContainsString('On branch ' . $defaultBranch, $status);
        $this->assertStringContainsString('nothing to commit', $status);

        // Test HTML status
        $htmlStatus = $repo->status(true);
        $this->assertStringContainsString('<br />', $htmlStatus);

        // Test branch operations with force
        $repo->createBranch('temp-branch');
        $repo->checkout('temp-branch');
        file_put_contents($repoPath . '/temp.txt', 'Temp content');
        $repo->add('.');
        $repo->commit('Add temp file');

        // Switch back to main branch before attempting deletion
        $repo->checkout($defaultBranch);
        $this->assertEquals($defaultBranch, $repo->activeBranch());

        // Try to delete unmerged branch (should fail)
        try {
            $repo->deleteBranch('temp-branch');
            $this->fail('Expected exception when deleting unmerged branch was not thrown');
        } catch (Exception $e) {
            $errorMessage = strtolower($e->getMessage());
            $this->assertTrue(
                str_contains($errorMessage, 'not fully merged') ||
                str_contains($errorMessage, 'cannot delete') ||
                str_contains($errorMessage, 'used by worktree'),
                sprintf(
                    'Exception message "%s" does not contain expected branch deletion error text',
                    $errorMessage
                )
            );
        }

        // Force delete branch
        $repo->deleteBranch('temp-branch', true);
        $this->assertNotContains('temp-branch', $repo->listBranches());

        // Test remote operations (using a real remote repository)
        $remoteRepo = 'https://github.com/octocat/Hello-World.git';

        // Test fetch
        try {
            $repo->fetch();
            $this->fail('Expected exception when fetching without remote was not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString('remote', strtolower($e->getMessage()));
        }

        // Test push/pull (should fail as we don't have a remote)
        try {
            $repo->push();
            $this->fail('Expected exception when pushing without remote was not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString('remote', strtolower($e->getMessage()));
        }

        try {
            $repo->pull();
            $this->fail('Expected exception when pulling without remote was not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString('remote', strtolower($e->getMessage()));
        }

        // Test environment variables
        $repo->setenv('GIT_AUTHOR_NAME', 'Test Author');
        $repo->setenv('GIT_AUTHOR_EMAIL', 'test@example.com');

        file_put_contents($repoPath . '/env-test.txt', 'Test environment variables');
        $repo->add('.');
        $repo->commit('Test environment variables');

        $log = $repo->log();
        $this->assertStringContainsString('Test Author', $log);
    }

    /**
     * Helper method to recursively delete a directory.
     *
     * @param string $dir
     */
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
}
