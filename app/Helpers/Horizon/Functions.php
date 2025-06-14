<?php

declare(strict_types=1);

use Laravel\Horizon\Contracts\MasterSupervisorRepository;

if (!function_exists('getHorizonStatus')) {
    /**
     * Get the horizon status.
     *
     * @return string
     */
    function getHorizonStatus(): string
    {
        if (! $masters = app(MasterSupervisorRepository::class)->all()) {
            return 'inactive';
        }

        return collect($masters)->every(function ($master) {
            return $master->status === 'paused';
        }) ? 'paused' : 'running';
    }
}
