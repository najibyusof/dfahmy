<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SystemHeartbeatCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'system:heartbeat';

    /**
     * @var string
     */
    protected $description = 'Store scheduler heartbeat timestamp for operations health monitoring';

    public function handle(): int
    {
        Cache::forever('system.scheduler_heartbeat_at', now()->toIso8601String());

        $this->info('Scheduler heartbeat updated.');

        return self::SUCCESS;
    }
}
