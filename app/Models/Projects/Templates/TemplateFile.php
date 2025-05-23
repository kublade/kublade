<?php

declare(strict_types=1);

namespace App\Models\Projects\Templates;

use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\ReservedPort;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Blade;

/**
 * Class TemplateFile.
 *
 * This class is the model for template files.
 *
 * @OA\Schema(
 *     schema="TemplateFile",
 *     type="object",
 *
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="template_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="template_directory_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000", nullable=true),
 *     @OA\Property(property="name", type="string", example="File 1"),
 *     @OA\Property(property="mime_type", type="string", example="text/plain"),
 *     @OA\Property(property="content", type="string", example="Content of the file"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example="2021-01-01 00:00:00", nullable=true),
 * )
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

    /**
     * Interpret the file.
     *
     * @param Deployment $deployment
     * @param array      $data
     * @param array      $secretData
     * @param array      $portClaims
     * @param bool       $paused
     *
     * @return string
     */
    public function interpret(Deployment $deployment): string
    {
        $publicData = [];

        $deployment->deploymentData->each(function ($data) use (&$publicData) {
            $publicData[$data->key] = $data->value;
        });

        $secretData = [];

        $deployment->deploymentSecretData->each(function ($data) use (&$secretData) {
            $secretData[$data->key] = $data->value;
        });

        $reservedPorts = $deployment->ports()->whereNotNull('claim')->get();
        $portClaims    = $deployment->template->ports()
            ->whereNotNull('claim')
            ->get()
            ->mapWithKeys(function (TemplatePort $port) use ($deployment, $reservedPorts) {
                $reservedPort = $reservedPorts->where('claim', '=', $port->claim)->first();

                if (!$reservedPort) {
                    $reservedPort = ReservedPort::create([
                        'deployment_id' => $deployment->id,
                        'group'         => $port->group,
                        'claim'         => $port->claim,
                        'port'          => ReservedPort::random($port->group),
                    ]);
                }

                return [$reservedPort->claim => $reservedPort->port];
            })
            ->toArray();

        return Blade::render($this->content, [
            'data'   => $publicData,
            'secret' => $secretData,
            'limits' => [
                'enabled' => $deployment->limit?->is_active ? 'true' : 'false',
                'cpu'     => $deployment->limit?->cpu,
                'memory'  => $deployment->limit?->memory,
            ],
            'portClaims' => $portClaims,
            'paused'     => $deployment->paused,
        ]);
    }
}
