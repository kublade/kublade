<?php

declare(strict_types=1);

use App\Jobs\Cluster\Dispatchers\LimitMonitoring as ClusterLimitMonitoring;
use App\Jobs\Cluster\Dispatchers\StatusMonitoring as ClusterStatusMonitoring;
use App\Jobs\Flux\Actions\FTPDeploymentDeletion as FluxDeploymentFTPDeletion;
use App\Jobs\Flux\Actions\PhpmyadminDeploymentDeletion as FluxDeploymentPhpmyadminDeletion;
use App\Jobs\Flux\Actions\StatusMonitoring as FluxDeploymentStatusMonitoring;
use App\Jobs\Flux\Dispatchers\DeploymentCreation as FluxDeploymentCreation;
use App\Jobs\Flux\Dispatchers\DeploymentDeletion as FluxDeploymentDeletion;
use App\Jobs\Flux\Dispatchers\DeploymentUpdate as FluxDeploymentUpdate;
use Illuminate\Support\Facades\Schedule;

Schedule::command('horizon:snapshot')->everyFiveMinutes();

Schedule::job(new ClusterLimitMonitoring(), 'dispatchers')->hourly();
Schedule::job(new ClusterStatusMonitoring(), 'dispatchers')->everyTenMinutes();
Schedule::job(new FluxDeploymentFTPDeletion(), 'singletons')->everyMinute();
Schedule::job(new FluxDeploymentPhpmyadminDeletion(), 'singletons')->everyMinute();
Schedule::job(new FluxDeploymentCreation(), 'dispatchers')->everyMinute();
Schedule::job(new FluxDeploymentDeletion(), 'dispatchers')->everyMinute();
Schedule::job(new FluxDeploymentUpdate(), 'dispatchers')->everyMinute();
Schedule::job(new FluxDeploymentStatusMonitoring(), 'singletons')->everyMinute();
