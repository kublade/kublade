<?php

declare(strict_types=1);

namespace App\Jobs\Base;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class Job.
 *
 * Class for identifying jobs. It is used as parent for every job.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
abstract class Job implements ShouldQueue
{
    /**
     * Identify jobs as queueable objects.
     */
    use DispatchesJobs;

    use Queueable;
    use InteractsWithQueue;
}
