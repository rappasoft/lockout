<?php

namespace Rappasoft\Lockout\Tests;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Rappasoft\Lockout\Events\LockoutDisabled;
use Rappasoft\Lockout\Events\LockoutEnabled;

class CommandsTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/lockout-'.bin2hex(random_bytes(8));
        mkdir($this->directory);
        mkdir($this->directory.'/cache');
        $this->app->useEnvironmentPath($this->directory);
        $this->app->useBootstrapPath($this->directory);
        Cache::flush();
        Event::fake([LockoutEnabled::class, LockoutDisabled::class]);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/cache/*') as $file) {
            unlink($file);
        }
        if (is_file($this->directory.'/.env')) {
            unlink($this->directory.'/.env');
        }
        rmdir($this->directory.'/cache');
        rmdir($this->directory);
        parent::tearDown();
    }

    public function testEnablePersistsStatusAndClearsBothCaches()
    {
        file_put_contents($this->directory.'/.env', "APP_NAME=Lockout\nAPP_READ_ONLY=false\n");
        chmod($this->directory.'/.env', 0600);
        file_put_contents($this->app->getCachedConfigPath(), '<?php return [];');
        Cache::put('lockout.status', false);

        $this->artisan('lockout:enable')->assertSuccessful();

        $this->assertSame("APP_NAME=Lockout\nAPP_READ_ONLY=true\n", file_get_contents($this->directory.'/.env'));
        $this->assertSame(0600, fileperms($this->directory.'/.env') & 0777);
        $this->assertTrue(config('lockout.enabled'));
        $this->assertFalse(Cache::has('lockout.status'));
        $this->assertFileDoesNotExist($this->app->getCachedConfigPath());
        Event::assertDispatched(LockoutEnabled::class);
    }

    public function testDisablePersistsStatusAndClearsTheStatusCache()
    {
        file_put_contents($this->directory.'/.env', "APP_NAME=Lockout\nAPP_READ_ONLY=true\n");
        config(['lockout.enabled' => true, 'lockout.cache_key' => 'custom.lockout']);
        Cache::put('custom.lockout', true);

        $this->artisan('lockout:disable')->assertSuccessful();

        $this->assertSame("APP_NAME=Lockout\nAPP_READ_ONLY=false\n", file_get_contents($this->directory.'/.env'));
        $this->assertFalse(config('lockout.enabled'));
        $this->assertFalse(Cache::has('custom.lockout'));
        Event::assertDispatched(LockoutDisabled::class);
    }

    public function testCommandsFailWhenTheEnvironmentFileIsMissing()
    {
        $this->artisan('lockout:enable')->assertFailed();
        $this->artisan('lockout:disable')->assertFailed();
        Event::assertNotDispatched(LockoutEnabled::class);
        Event::assertNotDispatched(LockoutDisabled::class);
    }

    public function testFailedPersistenceDoesNotChangeStatusOrFireSuccessEvents()
    {
        $content = "APP_READ_ONLY=false\n";
        file_put_contents($this->directory.'/.env', $content);
        File::partialMock()->shouldReceive('replace')->andThrow(new \RuntimeException('Disk full'));
        config(['lockout.enabled' => false]);

        $this->artisan('lockout:enable')->assertFailed();

        $this->assertSame($content, file_get_contents($this->directory.'/.env'));
        $this->assertFalse(config('lockout.enabled'));
        Event::assertNotDispatched(LockoutEnabled::class);
    }
}
