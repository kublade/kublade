<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Services\ClusterProvider\AwsEksProvisioner;
use Illuminate\Support\Collection;

/**
 * Class ClusterProvisioning.
 *
 * This class is the helper for cluster provisioning.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ClusterProvisioning
{
    /**
     * Get the provisioners.
     *
     * @return Collection
     */
    public static function provisioners(): Collection
    {
        return collect([
            AwsEksProvisioner::getTechnicalName() => AwsEksProvisioner::class,
        ]);
    }
}
