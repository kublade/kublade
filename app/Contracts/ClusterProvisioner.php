<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Contracts\Interfaces\ClusterProvisionerMetaInterface;
use App\Models\Kubernetes\Clusters\Cluster;
use Illuminate\Support\Collection;

/**
 * Interface ClusterProvisioner.
 *
 * This interface defines the methods for provisioning Kubernetes clusters.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
interface ClusterProvisioner
{
    /**
     * Constructor.
     *
     * @param array $providerOptions
     */
    public function __construct(array $providerOptions = []);

    /**
     * Get the provider options validation.
     *
     * @return array
     */
    public static function getProviderOptionsValidation(): array;

    /**
     * Get the technical name of the cluster provisioner.
     *
     * @return string
     */
    public static function getTechnicalName(): string;

    /**
     * Get the name of the cluster provisioner.
     *
     * @return string
     */
    public static function getName(): string;

    /**
     * Get the description of the cluster provisioner.
     *
     * @return string
     */
    public static function getDescription(): string;

    /**
     * Get the options of the cluster provisioner.
     *
     * @return string
     */
    public static function getOptions(): array;

    /**
     * Create a Kubernetes cluster.
     *
     * @param array $options
     *
     * @return Collection<ClusterProvisionerMetaInterface>
     */
    public function create(array $options = []): Collection;

    /**
     * Delete the cluster.
     *
     * @param Cluster $cluster
     * @param string  $name
     *
     * @return bool
     */
    public function delete(Cluster $cluster): bool;
}
