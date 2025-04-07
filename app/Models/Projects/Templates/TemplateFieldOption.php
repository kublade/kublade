<?php

declare(strict_types=1);

namespace App\Models\Projects\Templates;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TemplateFieldOption.
 *
 * This class is the model for template field options.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property string     $id
 * @property string     $template_field_id
 * @property string     $label
 * @property string     $value
 * @property float|null $amount
 * @property bool       $default
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 * @property Carbon     $deleted_at
 */
class TemplateFieldOption extends Model
{
    use SoftDeletes;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'template_field_options';

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
        'default' => 'boolean',
    ];

    /**
     * Relation to template field.
     *
     * @return HasOne
     */
    public function field(): HasOne
    {
        return $this->hasOne(TemplateField::class, 'id', 'template_field_id');
    }
}
