<?php

declare(strict_types=1);

namespace App\Helpers\Flux;

use App\Exceptions\FluxException;
use App\Helpers\Kubernetes\YamlFormatter;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentLink;
use App\Models\Projects\Deployments\ReservedPort;
use App\Models\Projects\Templates\TemplatePort;
use Exception;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

/**
 * Class FluxDeployment.
 *
 * This class is the helper for the Flux deployment.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class FluxDeployment
{
    /**
     * Generate a deployment.
     *
     * @param Deployment $deployment
     * @param array      $data
     * @param array      $secretData
     * @param bool       $replaceExisting
     *
     * @return object
     */
    public static function generate(Deployment $deployment, array $data = [], array $secretData = [], bool $replaceExisting = false)
    {
        FluxRepository::clear($deployment->cluster);
        FluxRepository::open($deployment->cluster);

        if (Storage::disk('local')->exists($deployment->path) && !$replaceExisting) {
            throw new FluxException('Forbidden', 403);
        }

        if ($replaceExisting) {
            Storage::disk('local')->deleteDirectory($deployment->path);
        }

        $deploymentDirectoryStatus = Storage::disk('local')->makeDirectory($deployment->path);

        if (!$deploymentDirectoryStatus) {
            throw new FluxException('Server Error', 500);
        }

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

        $deployment->template->fullTree->each(function ($item) use ($deployment, $data, $secretData, $portClaims) {
            if ($item->type === 'file') {
                self::createFile($item, $deployment, $data, $secretData, $portClaims);
            } elseif ($item->type === 'folder') {
                self::createFolder($item, $deployment, $data, $secretData, $portClaims);
            }
        });

        if ($deployment->template->netpol) {
            $rules = $deployment->ingressAsTarget?->filter(function (DeploymentLink $link) {
                return $link->source?->uuid;
            }) ?? collect();

            $networkPolicyDeployed = Storage::disk('local')->put(
                $deployment->path . '/netpol.yaml',
                YamlFormatter::format(
                    Yaml::dump([
                        'apiVersion' => 'networking.k8s.io/v1',
                        'kind'       => 'NetworkPolicy',
                        'metadata'   => [
                            'name' => 'ingress',
                        ],
                        'spec' => [
                            'podSelector' => [
                                'matchLabels' => [
                                    'app' => [],
                                ],
                            ],
                            'ingress' => [
                                [
                                    'from' => array_values([
                                        ...($rules->isNotEmpty() ? $rules->map(function (DeploymentLink $link) {
                                            return [
                                                'namespaceSelector' => [
                                                    'matchLabels' => [
                                                        'kubernetes.io/metadata.name' => $link->source?->uuid,
                                                    ],
                                                ],
                                            ];
                                        })->toArray() : []),
                                        [
                                            'namespaceSelector' => [
                                                'matchLabels' => [
                                                    'kubernetes.io/metadata.name' => $deployment->uuid,
                                                ],
                                            ],
                                        ],
                                        ...($deployment->cluster->utilityNamespace ? [
                                            'namespaceSelector' => [
                                                'matchLabels' => [
                                                    'kubernetes.io/metadata.name' => $deployment->cluster->utilityNamespace->name,
                                                ],
                                            ],
                                        ] : []),
                                        ...($deployment->cluster->ingressNamespace && $deployment->cluster->ingressNamespace->name !== $deployment->cluster->utilityNamespace?->name ? [
                                            'namespaceSelector' => [
                                                'matchLabels' => [
                                                    'kubernetes.io/metadata.name' => $deployment->cluster->ingressNamespace->name,
                                                ],
                                            ],
                                        ] : []),
                                    ]),
                                ],
                            ],
                        ],
                    ], 10, 2)
                )
            );

            if (!$networkPolicyDeployed) {
                throw new FluxException('Server Error', 500);
            }
        }

        $kustomizationPath    = $deployment->path . '/kustomization.yaml';
        $kustomizationContent = Storage::disk('local')->exists($kustomizationPath) ?
            Yaml::parse(Storage::get($kustomizationPath)) :
            [];
        $kustomizationDeployed = Storage::disk('local')->put(
            $deployment->path . '/kustomization.yaml',
            YamlFormatter::format(
                Yaml::dump([
                    ...$kustomizationContent,
                    'apiVersion' => 'kustomize.config.k8s.io/v1beta1',
                    'kind'       => 'Kustomization',
                    'namespace'  => $deployment->uuid,
                    'resources'  => [
                        ...(isset($kustomizationContent['resources']) ? $kustomizationContent['resources'] : []),
                        ...(isset($kustomizationContent['resources']) && !in_array('netpol.yaml', $kustomizationContent['resources']) && $deployment->template->netpol ? ['netpol.yaml'] : []),
                        ...(isset($kustomizationContent['resources']) && !in_array('namespace.yaml', $kustomizationContent['resources']) ? ['namespace.yaml'] : []),
                    ],
                ], 10, 2)
            )
        );

        if (!$kustomizationDeployed) {
            throw new FluxException('Server Error', 500);
        }

        $namespacePath    = $deployment->path . '/namespace.yaml';
        $namespaceContent = YamlFormatter::format(
            Yaml::dump([
                'apiVersion' => 'v1',
                'kind'       => 'Namespace',
                'metadata'   => [
                    'name' => $deployment->uuid,
                ],
            ], 10, 2)
        );
        $namespaceDeployed = Storage::disk('local')->put($namespacePath, $namespaceContent);

        if (!$namespaceDeployed) {
            throw new FluxException('Server Error', 500);
        }

        $commit = FluxRepository::push($deployment, $replaceExisting ? 'update' : 'creation');

        FluxRepository::close();

        return $commit;
    }

    /**
     * Delete a deployment.
     *
     * @param Deployment $deployment
     *
     * @return object
     */
    public static function delete(Deployment $deployment)
    {
        FluxRepository::clear($deployment->cluster);
        FluxRepository::open($deployment->cluster);

        if (!Storage::disk('local')->exists($deployment->path)) {
            throw new FluxException('Not Found', 404);
        }

        if (!Storage::disk('local')->deleteDirectory($deployment->path)) {
            throw new FluxException('Server Error', 500);
        }

        $commit = FluxRepository::push($deployment, 'deletion');

        FluxRepository::close();

        return $commit;
    }

    /**
     * Create a folder.
     *
     * @param object     $item
     * @param Deployment $deployment
     * @param array      $data
     * @param array      $secretData
     * @param array      $portClaims
     */
    private static function createFolder(object $item, Deployment $deployment, array $data = [], array $secretData = [], array $portClaims = [])
    {
        $path = $deployment->path . $item->object->path;

        Storage::disk('local')->makeDirectory($path);

        $item->children?->each(function ($child) use ($deployment, $data, $secretData, $portClaims) {
            if ($child->type === 'file') {
                self::createFile($child, $deployment, $data, $secretData, $portClaims);
            } elseif ($child->type === 'folder') {
                self::createFolder($child, $deployment, $data, $secretData, $portClaims);
            }
        });
    }

    /**
     * Create a file.
     *
     * @param object     $item
     * @param Deployment $deployment
     * @param array      $data
     * @param array      $secretData
     * @param array      $portClaims
     */
    private static function createFile(object $item, Deployment $deployment, array $data = [], array $secretData = [], array $portClaims = [])
    {
        $path = $deployment->path . $item->object->path;

        try {
            $templateContent = Yaml::parse(
                Blade::render(
                    str_replace("\t", '  ', $item->object->content),
                    [
                        'data'   => $data,
                        'secret' => $secretData,
                        'limits' => [
                            'enabled' => $deployment->limit?->is_active ? 'true' : 'false',
                            'cpu'     => $deployment->limit?->cpu,
                            'memory'  => $deployment->limit?->memory,
                        ],
                        'portClaims' => $portClaims,
                        'paused'     => $deployment->paused,
                    ],
                    false
                )
            );

            if (isset($templateContent['metadata']['namespace'])) {
                $templateContent['metadata']['namespace'] = $deployment->uuid;
            }

            if (!$templateContent) {
                Storage::disk('local')->delete($path);

                return;
            }

            Storage::disk('local')->put($path, YamlFormatter::format(Yaml::dump($templateContent, 10, 2)));
        } catch (Exception $e) {
            throw new FluxException('Server Error', 500);
        }
    }
}
