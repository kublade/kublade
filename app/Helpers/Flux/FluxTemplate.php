<?php

declare(strict_types=1);

namespace App\Helpers\Flux;

use App\Exceptions\FluxException;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentLink;
use App\Models\Projects\Deployments\ReservedPort;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

use function str_contains;

class FluxTemplate extends Flux
{
    public static function list()
    {
        return self::removeTemplateBasePath(Storage::disk('local')->directories('flux-templates'));
    }

    public static function getStructure(string $template)
    {
        $templatePath = self::TEMPLATE_BASE_PATH . $template;

        if (!Storage::disk('local')->exists($templatePath)) {
            throw new FluxException('Not Found', 404);
        }

        $files = self::removeTemplateBasePath(Storage::disk('local')->allFiles($templatePath), $template);

        return (object) [
            'files' => $files->reject(function ($filePath) {
                return $filePath === 'port-claims.json';
            }),
            'directories' => self::removeTemplateBasePath(Storage::disk('local')->allDirectories($templatePath), $template),
            'port_claims' => $files->contains(function ($filePath) {
                return $filePath === 'port-claims.json';
            }),
        ];
    }

    public static function generate(Deployment $deployment, array $data = [], array $secretData = [], bool $replaceExisting = false)
    {
        FluxRepository::open();

        $templatePath        = self::TEMPLATE_BASE_PATH . $deployment->template;
        $templateStructure   = self::getStructure($deployment->template);
        $deploymentDirectory = self::DEPLOYMENT_BASE_PATH . $deployment->uuid;

        if (Storage::disk('local')->exists($deploymentDirectory) && !$replaceExisting) {
            throw new FluxException('Forbidden', 403);
        }

        if ($replaceExisting) {
            Storage::disk('local')->deleteDirectory($deploymentDirectory);
        }

        $deploymentDirectoryStatus = Storage::disk('local')->makeDirectory($deploymentDirectory);

        if (!$deploymentDirectoryStatus) {
            throw new FluxException('Server Error', 500);
        }

        collect($templateStructure->directories)->each(function ($directoryPath) use ($deploymentDirectory) {
            $deploymentDefinitionDirectoryStatus = Storage::disk('local')->makeDirectory($deploymentDirectory . '/' . $directoryPath);

            if (!$deploymentDefinitionDirectoryStatus) {
                throw new FluxException('Server Error', 500);
            }
        });

        $portClaims = [];

        if ($templateStructure->port_claims) {
            $reservedPorts = $deployment->ports()->whereNotNull('claim')->get();

            // Fulfill port claims
            $portClaimsContent = Storage::disk('local')->get($templatePath . '/port-claims.json');
            $portClaimsList    = collect(json_decode($portClaimsContent, true) ?? []);

            $portClaims = $portClaimsList->mapWithKeys(function (string $group, string $claim) use ($deployment, $reservedPorts) {
                if (
                    $reservedPort = $reservedPorts->find(function ($reservedPort) use ($claim) {
                        return $reservedPort->claim === $claim;
                    })
                ) {
                    return [$claim => $reservedPort->port];
                }

                $port = ReservedPort::random($group);
                ReservedPort::create([
                    'deployment_id' => $deployment->id,
                    'group'         => $group,
                    'claim'         => $claim,
                    'port'          => $port,
                ]);

                return [$claim => $port];
            })->toArray();
        }

        collect($templateStructure->files)->each(function ($filePath) use ($deployment, $templatePath, $deploymentDirectory, $data, $secretData, $portClaims) {
            $filePathObjects = explode('/', $filePath);
            $fileName        = end($filePathObjects);
            $isSealedSecret  = str_contains($fileName, self::SEALED_SECRET_FILENAME_SUFFIX);

            $templateContent = Storage::disk('local')->get($templatePath . '/' . $filePath);
            $templateContent = Blade::render($templateContent, [
                'data'   => $data,
                'secret' => $secretData,
                'limits' => [
                    'enabled' => $deployment->limit?->is_active ? 'true' : 'false',
                    'cpu'     => $deployment->limit?->cpu,
                    'memory'  => $deployment->limit?->memory,
                ],
                'portClaims' => $portClaims,
                'paused'     => $deployment->paused,
            ], false);

            if ($isSealedSecret) {
                // Define required variables
                $deploymentDefinitionFileStatus = Storage::disk('local')->put($deploymentDirectory . '/' . $filePath, $templateContent);

                if (!$deploymentDefinitionFileStatus) {
                    throw new FluxException('Server Error', 500);
                }

                $storagePath        = Storage::disk('local')->path('');
                $fullFileInputPath  = $storagePath . $deploymentDirectory . '/' . $filePath;
                $fullFileOutputPath = str_replace(self::SEALED_SECRET_FILENAME_SUFFIX, self::SEALED_SECRET_FILENAME_SUFFIX_TARGET, $storagePath . $deploymentDirectory . '/' . $filePath);
                $kubesealNamespace  = config('flux.kubeseal.namespace', 'kube-system');
                $kubesealController = config('flux.kubeseal.controller', 'sealed-secrets-controller');

                // Deploy kubeconfig.yaml
                $kubeconfigDeployed = Storage::disk('local')->put('kubeconfig.yaml', config('flux.cluster.authentication'));
                $kubeconfigPath     = Storage::disk('local')->path('kubeconfig.yaml');

                // Generate sealed secret
                $cmd            = 'cat ' . $fullFileInputPath . ' | kubeseal --controller-namespace ' . $kubesealNamespace . ' --controller-name ' . $kubesealController . ' --format yaml --kubeconfig ' . $kubeconfigPath . ' > ' . $fullFileOutputPath;
                $additionalPath = config('flux.cluster.environment');

                if ($additionalPath) {
                    $process = Process::fromShellCommandline($cmd, null, [
                        'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:' . $additionalPath,
                    ]);
                } else {
                    $process = Process::fromShellCommandline($cmd);
                }

                $processOutput = '';
                $captureOutput = function ($type, $line) use (&$processOutput) {
                    $processOutput .= $line;
                };
                $process->setTimeout(null)->run($captureOutput);

                if ($process->getExitCode()) {
                    throw new FluxException('Server Error', 500);
                }

                // Delete unencrypted secret file
                $unsealedSecretDeleted = Storage::disk('local')->delete($deploymentDirectory . '/' . $filePath);

                if (!$unsealedSecretDeleted) {
                    throw new FluxException('Server Error', 500);
                }
            } else {
                $deploymentDefinitionFileStatus = Storage::disk('local')->put($deploymentDirectory . '/' . $filePath, $templateContent);

                if (!$deploymentDefinitionFileStatus) {
                    throw new FluxException('Server Error', 500);
                }
            }
        });

        $rules = collect();

        if ($deployment->ingressAsTarget) {
            $rules = $deployment->ingressAsTarget->filter(function (DeploymentLink $link) {
                return $link->source?->uuid;
            });
        }

        $ruleContent = '';

        if ($rules?->isNotEmpty()) {
            $rules->each(function (DeploymentLink $link) use (&$ruleContent) {
                $ruleContent .= '
    - namespaceSelector:
        matchLabels:
          kubernetes.io/metadata.name: ' . $link->source?->uuid . '';
            });
        }

        if (
            $deployment->template !== 'ftp' &&
            $deployment->template !== 'phpmyadmin'
        ) {
            // Generate netpol.yaml content
            $networkPolicyContent = '---
kind: NetworkPolicy
apiVersion: networking.k8s.io/v1
metadata:
  name: ingress
  namespace: ' . $deployment->uuid . '
spec:
  podSelector:
    matchLabels:
  ingress:
  - from:' . $ruleContent . '
    - namespaceSelector:
        matchLabels:
          kubernetes.io/metadata.name: ' . $deployment->uuid . '
    - namespaceSelector:
        matchLabels:
          kubernetes.io/metadata.name: ' . config('flux.cluster.utility.namespace') . (config('flux.cluster.utility.namespace') !== config('flux.cluster.ingress.namespace') ? '
    - namespaceSelector:
        matchLabels:
          kubernetes.io/metadata.name: ' . config('flux.cluster.ingress.namespace') : '') . '
---
kind: NetworkPolicy
apiVersion: networking.k8s.io/v1
metadata:
  name: ftp
  namespace: ' . $deployment->uuid . '
spec:
  podSelector:
    matchLabels:
      app.kubernetes.io/name: sftp
  ingress:
  - {}
---
kind: NetworkPolicy
apiVersion: networking.k8s.io/v1
metadata:
  name: phpmyadmin
  namespace: ' . $deployment->uuid . '
spec:
  podSelector:
    matchLabels:
      app.kubernetes.io/name: phpmyadmin
  ingress:
  - {}';

            // Deploy netpol.yaml
            $networkPolicyDeployed = Storage::disk('local')->put($deploymentDirectory . '/netpol.yaml', $networkPolicyContent);

            // Add netpol.yaml to customization file
            $kustomizationContent  = preg_replace("/resources:\n/", "resources:\n- netpol.yaml\n", Storage::get($deploymentDirectory . '/kustomization.yaml'));
            $networkPolicyAppended = Storage::disk('local')->put($deploymentDirectory . '/kustomization.yaml', $kustomizationContent);

            if (!$networkPolicyDeployed || !$networkPolicyAppended) {
                throw new FluxException('Server Error', 500);
            }
        }

        $commit = FluxRepository::push($deployment->uuid, $replaceExisting ? 'update' : 'creation');
        FluxRepository::close();

        return (object) [
            'template' => $deployment->template,
            'uuid'     => $deployment->uuid,
            'commit'   => $commit,
        ];
    }
}
