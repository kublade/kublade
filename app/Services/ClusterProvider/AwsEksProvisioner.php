<?php

declare(strict_types=1);

namespace App\Services\ClusterProvider;

use App\Contracts\ClusterProvisioner;
use App\Models\Kubernetes\Clusters\Cluster;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Class AwsEksProvisioner.
 *
 * This class is the provisioner for AWS EKS clusters.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class AwsEksProvisioner implements ClusterProvisioner
{
    /**
     * The provider options.
     *
     * @var array
     */
    private array $providerOptions;

    /**
     * Constructor.
     *
     * @param array $providerOptions
     */
    public function __construct(array $providerOptions = [])
    {
        $validation = Validator::make($providerOptions, self::getProviderOptionsValidation());

        if ($validation->fails()) {
            throw new Exception('Invalid initialization options: ' . $validation->errors()->toJson());
        }

        $this->providerOptions = $providerOptions;
    }

    /**
     * Get the provider options validation.
     *
     * @return array
     */
    public static function getProviderOptionsValidation(): array
    {
        return [
            'region' => ['required', 'string', 'max:255', Rule::in([
                // Full list of regions: https://docs.aws.amazon.com/general/latest/gr/eks.html
                'us-east-2',
                'us-east-1',
                'us-west-1',
                'us-west-2',
                'af-south-1',
                'ap-east-1',
                'ap-south-2',
                'ap-southeast-3',
                'ap-southeast-5',
                'ap-southeast-4',
                'ap-south-1',
                'ap-northeast-3',
                'ap-northeast-2',
                'ap-southeast-1',
                'ap-southeast-2',
                'ap-east-2',
                'ap-southeast-7',
                'ap-northeast-1',
                'ca-central-1',
                'ca-west-1',
                'eu-central-1',
                'eu-west-1',
                'eu-west-2',
                'eu-south-1',
                'eu-west-3',
                'eu-south-2',
                'eu-north-1',
                'eu-central-2',
                'il-central-1',
                'mx-central-1',
                'me-south-1',
                'me-central-1',
                'sa-east-1',
                'us-gov-east-1',
                'us-gov-west-1',
            ])],
            'aws_access_key_id'     => ['required', 'string', 'max:255'],
            'aws_secret_access_key' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get the technical name of the cluster provisioner.
     *
     * @return string
     */
    public static function getTechnicalName(): string
    {
        return 'aws-eks';
    }

    /**
     * Get the name of the cluster provisioner.
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'AWS EKS';
    }

    /**
     * Get the description of the cluster provisioner.
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Provision a Kubernetes cluster on AWS EKS.';
    }

    /**
     * Get the options of the cluster provisioner.
     *
     * @return array
     */
    public static function getOptions(): array
    {
        return [
            'name' => (object) [
                'type'        => 'text',
                'required'    => true,
                'description' => 'The name of the cluster.',
                'default'     => 'kublade-cluster',
            ],
            'kubernetes_version' => (object) [
                'type'        => 'text',
                'required'    => true,
                'description' => 'The Kubernetes version of the cluster.',
                'default'     => '1.33',
            ],
            'cluster_iam_role_arn' => (object) [
                'type'        => 'text',
                'required'    => true,
                'description' => 'The IAM role ARN of the cluster.',
            ],
            'node_group_iam_role_arn' => (object) [
                'type'        => 'text',
                'required'    => true,
                'description' => 'The IAM role ARN of the node group.',
            ],
            'vpc_id' => (object) [
                'type'        => 'text',
                'required'    => true,
                'description' => 'The VPC ID of the cluster.',
            ],
            'subnet_ids' => (object) [
                'type'  => 'array',
                'items' => (object) [
                    'type'        => 'text',
                    'description' => 'The subnet ID of the cluster.',
                    'required'    => true,
                ],
                'required'    => true,
                'description' => 'The subnet IDs of the cluster.',
                'default'     => [],
            ],
        ];
    }

    /**
     * Create a Kubernetes cluster.
     *
     * @param array $options
     *
     * @return Cluster
     */
    public function create(array $options = []): Cluster
    {
        throw new Exception('Not implemented');
    }

    /**
     * Delete the cluster.
     *
     * @param Cluster $cluster
     *
     * @return bool
     */
    public function delete(Cluster $cluster): bool
    {
        throw new Exception('Not implemented');
    }
}
