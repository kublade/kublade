<?php

declare(strict_types=1);

namespace App\Jobs\Template\Dispatchers;

use App\Jobs\Base\Job;
use App\Jobs\Template\Actions\GitImport as GitImportJob;
use App\Models\Projects\Templates\Template;
use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * Class GitImport.
 *
 * This class is the dispatcher job for processing git import.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class GitImport extends Job implements ShouldBeUnique
{
    public static $onQueue = 'dispatchers';

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        Template::query()->each(function (Template $template) {
            $this->dispatch((new GitImportJob([
                'template_id' => $template->id,
            ]))->onQueue('dispatchers'));
        });
    }

    /**
     * Define tags which the job can be identified by.
     *
     * @return array
     */
    public function tags(): array
    {
        return [
            'job',
            'job:template',
            'job:template:dispatcher',
            'job:template:dispatcher:GitImport',
        ];
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'template-git-import';
    }
}
