<?php

declare(strict_types=1);

use App\Jobs\Cluster\Dispatchers\StatusMonitoring;
use Illuminate\Support\Facades\Schedule;

Schedule::command('horizon:snapshot')->everyFiveMinutes();

Schedule::job(new StatusMonitoring(), 'dispatchers')->everyTenMinutes();
