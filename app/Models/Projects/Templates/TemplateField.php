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
 * Class TemplateField.
 *
 * This class is the model for template fields.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string      $id
 * @property string      $template_id
 * @property string      $type
 * @property bool        $required
 * @property string      $label
 * @property string      $key
 * @property string|null $value
 * @property float|null  $amount
 * @property float|null  $min
 * @property float|null  $max
 * @property float|null  $step
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon      $deleted_at
 */
class TemplateField extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'template_fields';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'required' => 'boolean',
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
     * Relation to template field options.
     *
     * @return HasMany
     */
    public function options(): HasMany
    {
        return $this->hasMany(TemplateFieldOption::class, 'template_field_id', 'id');
    }
}
