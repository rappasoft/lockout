<?php

namespace Rappasoft\Lockout\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class LockoutStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lockout:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display the current lockout status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $enabled = config('lockout.enabled', false);
        $cached = Cache::has(config('lockout.cache_key', 'lockout.status'));

        $this->info('Lockout Status:');
        $this->line('─────────────────');
        $this->line('Enabled: '.($enabled ? '<fg=green>Yes</>' : '<fg=red>No</>'));
        $this->line('Cached: '.($cached ? '<fg=yellow>Yes</>' : '<fg=gray>No</>'));

        if ($enabled) {
            $this->newLine();
            $this->info('Configuration:');
            $this->line('  Allow Login: '.(config('lockout.allow_login') ? 'Yes' : 'No'));
            $this->line('  Locked Types: '.implode(', ', config('lockout.locked_types', [])));
            $this->line('  Response Type: '.config('lockout.response_type', 'abort'));
            $this->line('  Health Check: '.(config('lockout.health_check_enabled') ? 'Yes' : 'No'));
            $this->line('  Cache Enabled: '.(config('lockout.cache_enabled') ? 'Yes' : 'No'));
        }

        return Command::SUCCESS;
    }
}
