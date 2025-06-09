<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\PermissionSet;
use Illuminate\Console\Command;

/**
 * Class SyncPermissions.
 *
 * This class is the command to sync the permissions.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kbl:permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        PermissionSet::sync();
    }
}
