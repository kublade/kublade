<?php

declare(strict_types=1);

namespace App\Models\Projects\Templates;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TemplateFile.
 *
 * This class is the model for template files.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string $id
 * @property string $template_id
 * @property string $template_directory_id
 * @property string $name
 * @property string $mime_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class TemplateFile extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'template_files';

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
    public function directory(): HasOne
    {
        return $this->hasOne(TemplateDirectory::class, 'id', 'template_directory_id');
    }

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
     * Get the tree attribute.
     *
     * @return object
     */
    public function getTreeAttribute(): object
    {
        return (object) [
            'type'      => 'file',
            'id'        => $this->id,
            'name'      => $this->name,
            'mime_type' => $this->mime_type,
        ];
    }

    /**
     * Get the full tree attribute.
     *
     * @return object
     */
    public function getFullTreeAttribute(): object
    {
        return (object) [
            'type'   => 'file',
            'object' => $this,
        ];
    }

    /**
     * Get the path attribute.
     *
     * @return string
     */
    public function getPathAttribute(): string
    {
        return $this->directory ? $this->directory->path . '/' . $this->name : '/' . $this->name;
    }
}
