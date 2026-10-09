<?php

namespace Rappasoft\Lockout\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Rappasoft\Lockout\Events\LockoutDisabled;

class LockoutDisable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lockout:disable {--clear-cache : Clear the lockout cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable the application lockout (read-only mode)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Update .env file
        $envFile = $this->laravel->environmentFilePath();

        if (! is_file($envFile)) {
            $this->error('The application environment file does not exist.');
            return Command::FAILURE;
        }

        try {
            $envContent = File::get($envFile);

            // Update or add APP_READ_ONLY
            if (preg_match('/^APP_READ_ONLY=(.*)$/m', $envContent)) {
                $envContent = preg_replace('/^APP_READ_ONLY=(.*)$/m', 'APP_READ_ONLY=false', $envContent);
            } else {
                $envContent .= "\nAPP_READ_ONLY=false\n";
            }

            File::replace($envFile, $envContent, fileperms($envFile) & 0777);

            if (is_file($this->laravel->getCachedConfigPath())) {
                $this->callSilent('config:clear');
                if (is_file($this->laravel->getCachedConfigPath())) {
                    throw new \RuntimeException('The configuration cache could not be cleared.');
                }
            }
        } catch (\Throwable $exception) {
            $this->error('Unable to disable lockout: '.$exception->getMessage());
            return Command::FAILURE;
        }

        Cache::forget(config('lockout.cache_key', 'lockout.status'));
        if ($this->option('clear-cache')) {
            $this->info('Lockout cache cleared.');
        }

        config(['lockout.enabled' => false]);

        // Fire event
        if (config('lockout.fire_events', true)) {
            event(new LockoutDisabled());
        }

        $this->info('Application lockout has been disabled.');

        return Command::SUCCESS;
    }
}
