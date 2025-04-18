<?php

declare(strict_types=1);

namespace App\Models\Projects\Templates;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TemplateDirectory.
 *
 * This class is the model for template directories.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string      $id
 * @property string      $template_id
 * @property string|null $parent_id
 * @property string      $name
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon      $deleted_at
 */
class TemplateDirectory extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'template_directories';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to template.
     *
     * @return HasOne
     */
    public function template(): HasOne
    {
        return $this->hasOne(Template::class, 'id', 'template_id');
    }

    /**
     * Relation to parent directory.
     *
     * @return HasOne
     */
    public function parent(): HasOne
    {
        return $this->hasOne(TemplateDirectory::class, 'id', 'parent_id');
    }

    /**
     * Relation to folders.
     *
     * @return HasMany
     */
    public function folders(): HasMany
    {
        return $this->hasMany(TemplateDirectory::class, 'parent_id', 'id');
    }

    /**
     * Relation to files.
     *
     * @return HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(TemplateFile::class, 'template_directory_id', 'id');
    }

    /**
     * Get the tree attribute.
     *
     * @return object
     */
    public function getTreeAttribute(): object
    {
        $subFolders = $this->folders->map(function ($child) {
            return $child->tree;
        })->toArray() ?? [];
        $subFiles = $this->files->map(function ($file) {
            return $file->tree;
        })->toArray() ?? [];

        return (object) [
            'type'     => 'folder',
            'id'       => $this->id,
            'name'     => $this->name,
            'children' => collect([
                ...$subFolders,
                ...$subFiles,
            ]),
        ];
    }

    /**
     * Get the path attribute.
     *
     * @return string
     */
    public function getPathAttribute(): string
    {
        return $this->parent ? $this->parent->path . '/' . $this->name : '/' . $this->name;
    }
}
