<?php

declare(strict_types=1);

namespace App\Helpers\Git;

use Exception;

/**
 * Git Repository Interface Class.
 *
 * This class enables the creating, reading, and manipulation
 * of a git repository
 *
 * @class  GitRepo
 */
class GitRepo
{
    protected $repoPath = null;

    protected $bare = false;

    protected $envopts = [];

    /**
     * Create a new git repository.
     *
     * Accepts a creation path, and, optionally, a source path
     *
     * @param mixed      $repoPath
     * @param mixed|null $source
     * @param mixed      $remoteSource
     * @param mixed|null $reference
     * @param   string  repository path
     * @param   string  directory to source
     * @param   string  reference path
     *
     * @return GitRepo
     */
    public static function createNew($repoPath, $source = null, $remoteSource = false, $reference = null)
    {
        if (is_dir($repoPath) && file_exists($repoPath . '/.git')) {
            throw new Exception('"' . $repoPath . '" is already a git repository');
        } else {
            $repo = new self($repoPath, true, false);

            if (is_string($source)) {
                if ($remoteSource) {
                    if (isset($reference)) {
                        if (!is_dir($reference) || !is_dir($reference . '/.git')) {
                            throw new Exception('"' . $reference . '" is not a git repository. Cannot use as reference.');
                        } elseif (strlen($reference)) {
                            $reference = realpath($reference);
                            $reference = "--reference $reference";
                        }
                    }
                    $repo->cloneRemote($source, $reference);
                } else {
                    $repo->cloneFrom($source);
                }
            } else {
                $repo->run('init');
            }

            return $repo;
        }
    }

    /**
     * Constructor.
     *
     * Accepts a repository path
     *
     * @param mixed|null $repoPath
     * @param mixed      $createNew
     * @param mixed      $_init
     * @param   string  repository path
     * @param   bool    create if not exists?
     */
    public function __construct($repoPath = null, $createNew = false, $_init = true)
    {
        if (is_string($repoPath)) {
            $this->setRepoPath($repoPath, $createNew, $_init);
        }
    }

    /**
     * Set the repository's path.
     *
     * Accepts the repository path
     *
     * @param mixed $repoPath
     * @param mixed $createNew
     * @param mixed $_init
     * @param   string  repository path
     * @param   bool    create if not exists?
     * @param   bool    initialize new Git repo if not exists?
     */
    public function setRepoPath($repoPath, $createNew = false, $_init = true)
    {
        if (is_string($repoPath)) {
            if ($newPath = realpath($repoPath)) {
                $repoPath = $newPath;

                if (is_dir($repoPath)) {
                    // Is this a work tree?
                    if (file_exists($repoPath . '/.git')) {
                        $this->repoPath = $repoPath;
                        $this->bare     = false;
                        // Is this a bare repo?
                    } elseif (is_file($repoPath . '/config')) {
                        $parse_ini = parse_ini_file($repoPath . '/config');

                        if ($parse_ini['bare']) {
                            $this->repoPath = $repoPath;
                            $this->bare     = true;
                        }
                    } else {
                        if ($createNew) {
                            $this->repoPath = $repoPath;

                            if ($_init) {
                                $this->run('init');
                            }
                        } else {
                            throw new Exception('"' . $repoPath . '" is not a git repository');
                        }
                    }
                } else {
                    throw new Exception('"' . $repoPath . '" is not a directory');
                }
            } else {
                if ($createNew) {
                    if ($parent = realpath(dirname($repoPath))) {
                        mkdir($repoPath);
                        $this->repoPath = $repoPath;

                        if ($_init) {
                            $this->run('init');
                        }
                    } else {
                        throw new Exception('cannot create repository in non-existent directory');
                    }
                } else {
                    throw new Exception('"' . $repoPath . '" does not exist');
                }
            }
        }
    }

    /**
     * Get the path to the git repo directory (eg. the ".git" directory).
     *
     * @return string
     */
    public function gitDirectoryPath()
    {
        if ($this->bare) {
            return $this->repoPath;
        } elseif (is_dir($this->repoPath . '/.git')) {
            return $this->repoPath . '/.git';
        } elseif (is_file($this->repoPath . '/.git')) {
            $gitFile = file_get_contents($this->repoPath . '/.git');

            if (mb_ereg('^gitdir: (.+)$', $gitFile, $matches)) {
                if ($matches[1]) {
                    $relGitPath = $matches[1];

                    return $this->repoPath . '/' . $relGitPath;
                }
            }
        }

        throw new Exception('could not find git dir for ' . $this->repoPath . '.');
    }

    /**
     * Tests if git is installed.
     *
     * @return bool
     */
    public function testGit()
    {
        $descriptorspec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $pipes    = [];
        $resource = proc_open(Git::getBin(), $descriptorspec, $pipes);

        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $status = trim(proc_close($resource));

        return $status != 127;
    }

    /**
     * Run a command in the git repository.
     *
     * Accepts a shell command to run
     *
     * @param mixed $command
     * @param   string  command to run
     *
     * @return string
     */
    protected function runCommand($command)
    {
        $descriptorspec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $pipes = [];

        /* Depending on the value of variables_order, $_ENV may be empty.
         * In that case, we have to explicitly set the new variables with
         * putenv, and call proc_open with env=null to inherit the reset
         * of the system.
         *
         * This is kind of crappy because we cannot easily restore just those
         * variables afterwards.
         *
         * If $_ENV is not empty, then we can just copy it and be done with it.
         */
        if (count($_ENV) === 0) {
            $env = null;

            foreach ($this->envopts as $k => $v) {
                putenv(sprintf('%s=%s', $k, $v));
            }
        } else {
            $env = array_merge($_ENV, $this->envopts);
        }
        $cwd      = $this->repoPath;
        $resource = proc_open($command, $descriptorspec, $pipes, $cwd, $env);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $status = trim(proc_close($resource));

        if ($status) {
            throw new Exception($stderr . "\n" . $stdout);
        } //Not all errors are printed to stderr, so include std out as well.

        return $stdout;
    }

    /**
     * Run a git command in the git repository.
     *
     * Accepts a git command to run
     *
     * @param mixed $command
     * @param   string  command to run
     *
     * @return string
     */
    public function run($command)
    {
        return $this->runCommand(Git::getBin() . ' ' . $command);
    }

    /**
     * Runs a 'git status' call.
     *
     * Accept a convert to HTML bool
     *
     * @param mixed $html
     * @param bool  return string with <br />
     *
     * @return string
     */
    public function status($html = false)
    {
        $msg = $this->run('status');

        if ($html == true) {
            $msg = str_replace("\n", '<br />', $msg);
        }

        return $msg;
    }

    /**
     * Runs a `git add` call.
     *
     * Accepts a list of files to add
     *
     * @param mixed $files
     * @param   mixed   files to add
     *
     * @return string
     */
    public function add($files = '*')
    {
        if (is_array($files)) {
            $files = '"' . implode('" "', $files) . '"';
        }

        return $this->run("add $files -v");
    }

    /**
     * Runs a `git rm` call.
     *
     * Accepts a list of files to remove
     *
     * @param mixed $files
     * @param mixed $cached
     * @param   mixed    files to remove
     * @param   bool  use the --cached flag?
     *
     * @return string
     */
    public function rm($files = '*', $cached = false)
    {
        if (is_array($files)) {
            $files = '"' . implode('" "', $files) . '"';
        }

        return $this->run('rm ' . ($cached ? '--cached ' : '') . $files);
    }

    /**
     * Runs a `git commit` call.
     *
     * Accepts a commit message string
     *
     * @param mixed $message
     * @param mixed $commitAll
     * @param   string  commit message
     * @param   bool  should all files be committed automatically (-a flag)
     *
     * @return string
     */
    public function commit($message = '', $commitAll = true)
    {
        $flags = $commitAll ? '-av' : '-v';

        return $this->run('commit ' . $flags . ' -m ' . escapeshellarg($message));
    }

    /**
     * Runs a `git clone` call to clone the current repository
     * into a different directory.
     *
     * Accepts a target directory
     *
     * @param mixed $target
     * @param   string  target directory
     *
     * @return string
     */
    public function cloneTo($target)
    {
        return $this->run('clone --local ' . $this->repoPath . " $target");
    }

    /**
     * Runs a `git clone` call to clone a different repository
     * into the current repository.
     *
     * Accepts a source directory
     *
     * @param mixed $source
     * @param   string  source directory
     *
     * @return string
     */
    public function cloneFrom($source)
    {
        return $this->run("clone --local $source " . $this->repoPath);
    }

    /**
     * Runs a `git clone` call to clone a remote repository
     * into the current repository.
     *
     * Accepts a source url
     *
     * @param mixed $source
     * @param mixed $reference
     * @param   string  source url
     * @param   string  reference path
     *
     * @return string
     */
    public function cloneRemote($source, $reference)
    {
        return $this->run("clone $reference $source " . $this->repoPath);
    }

    /**
     * Runs a `git clean` call.
     *
     * Accepts a remove directories flag
     *
     * @param mixed $dirs
     * @param mixed $force
     * @param   bool    delete directories?
     * @param   bool    force clean?
     *
     * @return string
     */
    public function clean($dirs = false, $force = false)
    {
        return $this->run('clean' . (($force) ? ' -f' : '') . (($dirs) ? ' -d' : ''));
    }

    /**
     * Runs a `git branch` call.
     *
     * Accepts a name for the branch
     *
     * @param mixed $branch
     * @param   string  branch name
     *
     * @return string
     */
    public function createBranch($branch)
    {
        return $this->run('branch ' . escapeshellarg($branch));
    }

    /**
     * Runs a `git branch -[d|D]` call.
     *
     * Accepts a name for the branch
     *
     * @param mixed $branch
     * @param mixed $force
     * @param   string  branch name
     *
     * @return string
     */
    public function deleteBranch($branch, $force = false)
    {
        return $this->run('branch ' . (($force) ? '-D' : '-d') . " $branch");
    }

    /**
     * Runs a `git branch` call.
     *
     * @param mixed $keep_asterisk
     * @param   bool    keep asterisk mark on active branch
     *
     * @return array
     */
    public function listBranches($keep_asterisk = false)
    {
        $branchArray = explode("\n", $this->run('branch'));

        foreach ($branchArray as $i => &$branch) {
            $branch = trim($branch);

            if (! $keep_asterisk) {
                $branch = str_replace('* ', '', $branch);
            }

            if ($branch == '') {
                unset($branchArray[$i]);
            }
        }

        return $branchArray;
    }

    /**
     * Lists remote branches (using `git branch -r`).
     *
     * Also strips out the HEAD reference (e.g. "origin/HEAD -> origin/master").
     *
     * @return array
     */
    public function listRemoteBranches()
    {
        $branchArray = explode("\n", $this->run('branch -r'));

        foreach ($branchArray as $i => &$branch) {
            $branch = trim($branch);

            if ($branch == '' || strpos($branch, 'HEAD -> ') !== false) {
                unset($branchArray[$i]);
            }
        }

        return $branchArray;
    }

    /**
     * Returns name of active branch.
     *
     * @param mixed $keep_asterisk
     * @param   bool    keep asterisk mark on branch name
     *
     * @return string
     */
    public function activeBranch($keep_asterisk = false)
    {
        $branchArray   = $this->listBranches(true);
        $active_branch = preg_grep("/^\*/", $branchArray);
        reset($active_branch);

        if ($keep_asterisk) {
            return current($active_branch);
        } else {
            return str_replace('* ', '', current($active_branch));
        }
    }

    /**
     * Runs a `git checkout` call.
     *
     * Accepts a name for the branch
     *
     * @param mixed $branch
     * @param   string  branch name
     *
     * @return string
     */
    public function checkout($branch)
    {
        return $this->run('checkout ' . escapeshellarg($branch));
    }

    /**
     * Runs a `git merge` call.
     *
     * Accepts a name for the branch to be merged
     *
     * @param string $branch
     *
     * @return string
     */
    public function merge($branch)
    {
        return $this->run('merge ' . escapeshellarg($branch) . ' --no-ff');
    }

    /**
     * Runs a git fetch on the current branch.
     *
     * @return string
     */
    public function fetch()
    {
        return $this->run('fetch');
    }

    /**
     * Add a new tag on the current position.
     *
     * Accepts the name for the tag and the message
     *
     * @param string $tag
     * @param string $message
     *
     * @return string
     */
    public function addTag($tag, $message = null)
    {
        if (is_null($message)) {
            $message = $tag;
        }

        return $this->run("tag -a $tag -m " . escapeshellarg($message));
    }

    /**
     * List all the available repository tags.
     *
     * Optionally, accept a shell wildcard pattern and return only tags matching it.
     *
     * @param string $pattern Shell wildcard pattern to match tags against.
     *
     * @return array Available repository tags.
     */
    public function listTags($pattern = null)
    {
        $tagArray = explode("\n", $this->run("tag -l $pattern"));

        foreach ($tagArray as $i => &$tag) {
            $tag = trim($tag);

            if (empty($tag)) {
                unset($tagArray[$i]);
            }
        }

        return $tagArray;
    }

    /**
     * Push specific branch (or all branches) to a remote.
     *
     * Accepts the name of the remote and local branch.
     * If omitted, the command will be "git push", and therefore will take
     * on the behavior of your "push.defualt" configuration setting.
     *
     * @param string $remote
     * @param string $branch
     *
     * @return string
     */
    public function push($remote = '', $branch = '')
    {
        //--tags removed since this was preventing branches from being pushed (only tags were)
        return $this->run("push $remote $branch");
    }

    /**
     * Pull specific branch from remote.
     *
     * Accepts the name of the remote and local branch.
     * If omitted, the command will be "git pull", and therefore will take on the
     * behavior as-configured in your clone / environment.
     *
     * @param string $remote
     * @param string $branch
     *
     * @return string
     */
    public function pull($remote = '', $branch = '')
    {
        return $this->run("pull $remote $branch");
    }

    /**
     * List log entries.
     *
     * @param string     $format
     * @param mixed      $fullDiff
     * @param mixed|null $filepath
     * @param mixed      $follow
     *
     * @return string
     */
    public function log($format = null, $fullDiff = false, $filepath = null, $follow = false)
    {
        $diff = '';

        if ($fullDiff) {
            $diff = '--full-diff -p ';
        }

        if ($follow) {
            // Can't use full-diff with follow
            $diff = '--follow -- ';
        }

        if ($format === null) {
            return $this->run('log ' . $diff . $filepath);
        } else {
            return $this->run('log --pretty=format:"' . $format . '" ' . $diff . $filepath);
        }
    }

    /**
     * Sets the project description.
     *
     * @param string $new
     */
    public function setDescription($new)
    {
        $path = $this->gitDirectoryPath();
        file_put_contents($path . '/description', $new);
    }

    /**
     * Gets the project description.
     *
     * @return string
     */
    public function getDescription()
    {
        $path = $this->gitDirectoryPath();

        return file_get_contents($path . '/description');
    }

    /**
     * Sets custom environment options for calling Git.
     *
     * @param mixed $key
     * @param mixed $value
     * @param string key
     * @param string value
     */
    public function setenv($key, $value)
    {
        $this->envopts[$key] = $value;
    }
}
