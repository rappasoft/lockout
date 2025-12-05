<?php

namespace Rappasoft\Lockout\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Rappasoft\Lockout\Events\LockoutEnabled;

class LockoutEnable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lockout:enable {--clear-cache : Clear the lockout cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable the application lockout (read-only mode)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Update .env file
        $envFile = base_path('.env');

        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);

            // Update or add APP_READ_ONLY
            if (preg_match('/^APP_READ_ONLY=(.*)$/m', $envContent)) {
                $envContent = preg_replace('/^APP_READ_ONLY=(.*)$/m', 'APP_READ_ONLY=true', $envContent);
            } else {
                $envContent .= "\nAPP_READ_ONLY=true\n";
            }

            file_put_contents($envFile, $envContent);
        }

        // Clear cache if requested
        if ($this->option('clear-cache')) {
            Cache::forget(config('lockout.cache_key', 'lockout.status'));
            $this->info('Lockout cache cleared.');
        }

        // Fire event
        if (config('lockout.fire_events', true)) {
            event(new LockoutEnabled());
        }

        // Reload config
        config(['lockout.enabled' => true]);

        $this->info('Application lockout has been enabled.');

        return Command::SUCCESS;
    }
}

