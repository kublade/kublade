<?php

declare(strict_types=1);

namespace App\Contracts\Interfaces;

/**
 * Interface ClusterProvisionerMetaInterface.
 *
 * This interface defines the methods for cluster provisioner meta.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
abstract class ClusterProvisionerMetaInterface
{
    public string $key;

    public string $value;
}
