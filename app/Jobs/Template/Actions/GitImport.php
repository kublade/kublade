<?php

declare(strict_types=1);

namespace App\Jobs\Template\Actions;

use App\Exceptions\TemplateException;
use App\Helpers\Template\TemplateRepository;
use App\Jobs\Base\Job;
use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateDirectory;
use App\Models\Projects\Templates\TemplateField;
use App\Models\Projects\Templates\TemplateFieldOption;
use App\Models\Projects\Templates\TemplateFile;
use App\Models\Projects\Templates\TemplatePort;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Class GitImport.
 *
 * This class is the action job for git import.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class GitImport extends Job implements ShouldBeUnique
{
    public static $onQueue = 'template_git_import';

    private string $template_id;

    private Collection $directories;

    private Collection $files;

    private Collection $fields;

    private Collection $fieldOptions;

    private Collection $ports;

    /**
     * LimitMonitoring constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->template_id  = $data['template_id'];
        $this->directories  = collect();
        $this->files        = collect();
        $this->fields       = collect();
        $this->fieldOptions = collect();
        $this->ports        = collect();
    }

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        $template = Template::find($this->template_id);

        if (
            !$template ||
            !$template->gitCredentials
        ) {
            return;
        }

        $template->gitCredentials->update([
            'synced_at' => null,
        ]);

        TemplateRepository::clear($template);
        TemplateRepository::open($template);

        if (!Storage::disk('local')->exists($template->repositoryImportPath)) {
            throw new TemplateException('Not Found', 404);
        }

        $this->startImportFromPath($template->repositoryImportPath);

        if (Storage::disk('local')->exists($template->path . '/.kublade/fields.json')) {
            $fields = json_decode(Storage::disk('local')->get($template->path . '/.kublade/fields.json'));

            collect($fields)->each(function (object $field) {
                $fieldObject = TemplateField::updateOrCreate([
                    'template_id' => $this->template_id,
                    'key'         => $field->key,
                ], [
                    'type'  => $field->type,
                    'label' => $field->label,
                    'value' => $field->value,
                    ...(isset($field->min) ? ['min' => $field->min] : []),
                    ...(isset($field->max) ? ['max' => $field->max] : []),
                    ...(isset($field->step) ? ['step' => $field->step] : []),
                    'required'      => isset($field->required) && $field->required,
                    'secret'        => isset($field->secret) && $field->secret,
                    'set_on_create' => isset($field->set_on_create) && $field->set_on_create,
                    'set_on_update' => isset($field->set_on_update) && $field->set_on_update,
                ]);

                $this->fields->push($fieldObject->id);

                if (isset($field->options)) {
                    collect($field->options)->each(function (object $option) use ($fieldObject) {
                        $optionObject = TemplateFieldOption::updateOrCreate([
                            'template_field_id' => $fieldObject->id,
                            'value'             => $option->value,
                        ], [
                            'label'   => $option->label,
                            'default' => isset($option->default) && $option->default,
                        ]);

                        $this->fieldOptions->push($optionObject->id);
                    });
                }
            });
        }

        if (Storage::disk('local')->exists($template->path . '/.kublade/ports.json')) {
            $ports = json_decode(Storage::disk('local')->get($template->path . '/.kublade/ports.json'));

            collect($ports)->each(function (object $port) {
                $portObject = TemplatePort::updateOrCreate([
                    'template_id' => $this->template_id,
                    'group'       => $port->group,
                    'claim'       => $port->claim,
                ], [
                    'preferred_port' => $port->preferred_port,
                    'random'         => $port->random,
                ]);

                $this->ports->push($portObject->id);
            });
        }

        TemplateRepository::close();

        TemplateFile::where('template_id', $this->template_id)
            ->whereNotIn('id', $this->files)
            ->delete();

        TemplateDirectory::where('template_id', $this->template_id)
            ->whereNotIn('id', $this->directories)
            ->delete();

        TemplateField::where('template_id', $this->template_id)
            ->whereNotIn('id', $this->fields)
            ->delete();

        TemplateFieldOption::whereHas('field', function (Builder $query) {
            $query->where('template_id', $this->template_id);
        })
            ->whereNotIn('id', $this->fieldOptions)
            ->delete();

        TemplatePort::where('template_id', $this->template_id)
            ->whereNotIn('id', $this->ports)
            ->delete();

        $template->gitCredentials->update([
            'synced_at' => Carbon::now(),
        ]);
    }

    /**
     * Start the import from a path.
     *
     * @param string                 $path
     * @param TemplateDirectory|null $parent
     */
    private function startImportFromPath(string $path, TemplateDirectory $parent = null)
    {
        collect(Storage::disk('local')->files($path))
            ->filter(function (string $file) {
                $file = basename($file);

                return $file !== '.gitignore' && $file !== '.gitkeep';
            })
            ->each(function (string $file) use ($parent) {
                $fileObject = TemplateFile::updateOrCreate([
                    'template_id'           => $this->template_id,
                    'template_directory_id' => $parent?->id,
                    'name'                  => basename($file),
                    'mime_type'             => str_ends_with($file, '.yaml') ? 'text/yaml' : 'application/octet-stream',
                ], [
                    'content' => Storage::disk('local')->get($file),
                ]);

                $this->files->push($fileObject->id);
            });

        collect(Storage::disk('local')->directories($path))
            ->filter(function (string $directory) {
                $directory = basename($directory);

                return $directory !== '.kublade' && $directory !== '.git';
            })
            ->each(function (string $directory) use ($path, $parent) {
                $directoryObject = TemplateDirectory::updateOrCreate([
                    'template_id' => $this->template_id,
                    'parent_id'   => $parent?->id,
                    'name'        => basename($directory),
                ]);

                $this->directories->push($directoryObject->id);

                $this->startImportFromPath($path . '/' . $directoryObject->name, $directoryObject);
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
            'job:template:' . $this->template_id,
            'job:template:' . $this->template_id . ':action',
            'job:template:' . $this->template_id . ':action:GitImport',
        ];
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'template-git-import-' . $this->template_id;
    }
}
