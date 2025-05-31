<?php

declare(strict_types=1);

namespace App\Helpers\Template;

use App\Exceptions\FluxException;
use App\Helpers\Git\Git;
use App\Helpers\Git\GitRepo;
use App\Models\Projects\Templates\Template;
use Exception;
use Illuminate\Support\Facades\Storage;

/**
 * Class TemplateRepository.
 *
 * This class is the helper for the template repository.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class TemplateRepository
{
    /**
     * The template.
     *
     * @var Template|null
     */
    public static $template;

    /**
     * The repository.
     *
     * @var GitRepo|null
     */
    public static $repository;

    /**
     * Open the Flux repository.
     *
     * @param Template $template
     */
    public static function open(Template $template)
    {
        if (!$template->gitCredentials) {
            throw new FluxException('Bad Request', 400);
        }

        self::$template = $template;

        $path          = 'templates/' . $template->id;
        $storagePath   = Storage::disk('local')->path($path);
        $repositoryUrl = $template->gitCredentials->url;

        if (
            str_starts_with($template->gitCredentials->url, 'http://') ||
            str_starts_with($template->gitCredentials->url, 'https://')
        ) {
            if (!str_contains($template->gitCredentials->url, '@')) {
                $repositoryUrl = preg_replace("/^(https?:\/\/)/", '$1' . $template->gitCredentials->credentials . '@', $repositoryUrl);
            }
        } else {
            $path = 'templates/' . $template->id . '/git-credentials';

            Storage::disk('local')->put($path, $template->gitCredentials->credentials);

            $sshKeyPath = Storage::disk('local')->path($path);
            $sshDir     = '~/.ssh';

            if (!is_dir($sshDir)) {
                mkdir($sshDir, 0700, true);
            }

            $sshConfig        = $sshDir . '/config';
            $sshConfigExisted = true;

            if (!file_exists($sshConfig)) {
                file_put_contents($sshConfig, '');
                $sshConfigExisted = false;
            }

            $sshConfigContent = file_get_contents($sshConfig);

            preg_match('/@(.*?):/', $repositoryUrl, $matches);

            if (count($matches) > 1) {
                if (!str_contains($sshConfigContent, 'Host ' . $template->id)) {
                    if ($sshConfigExisted) {
                        $content = $sshConfigContent . <<<EOL

Host $template->id
    HostName $matches[1]
    User git
    IdentityFile $sshKeyPath
EOL;
                    } else {
                        $content = <<<EOL
Host $template->id
    HostName $matches[1]
    User git
    IdentityFile $sshKeyPath
EOL;
                    }

                    file_put_contents($sshConfig, $content);
                }

                $repositoryUrl = str_replace($matches[1], $template->id, $repositoryUrl);
            }
        }

        if (!str_ends_with($repositoryUrl, '.git')) {
            $repositoryUrl .= '.git';
        }

        try {
            self::$repository = Git::open($storagePath);
        } catch (Exception $exception) {
            Storage::disk('local')->deleteDirectory($path);

            self::$repository = Git::cloneRemote($storagePath, $repositoryUrl);
        }

        self::$repository->run('config user.email "' . $template->gitCredentials->email . '"');
        self::$repository->run('config user.name "' . $template->gitCredentials->username . '"');
        self::$repository->pull('origin', $template->gitCredentials->branch);
    }

    /**
     * Get the Flux repository.
     *
     * @return GitRepo
     */
    public static function get(): GitRepo
    {
        return self::$repository;
    }

    /**
     * Close the Flux repository.
     */
    public static function close()
    {
        Storage::disk('local')->deleteDirectory(self::$template->path);

        self::$template   = null;
        self::$repository = null;
    }

    /**
     * Clear the template repository.
     *
     * @param Template $template
     */
    public static function clear(Template $template)
    {
        Storage::disk('local')->deleteDirectory($template->path);
    }
}
