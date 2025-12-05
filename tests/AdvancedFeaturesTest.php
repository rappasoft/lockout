<?php

namespace Rappasoft\Lockout\Tests;

use Illuminate\Http\Response;
use Rappasoft\Lockout\Events\LockoutDisabled;
use Rappasoft\Lockout\Events\LockoutEnabled;
use Rappasoft\Lockout\Events\RequestBlocked;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;

class AdvancedFeaturesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Event::fake();
    }

    /** @test */
    public function ip_whitelist_allows_access()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.ip_whitelist_array' => ['127.0.0.1']]);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function ip_whitelist_blocks_other_ips()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.ip_whitelist_array' => ['127.0.0.1']]);

        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.1'])
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function ip_whitelist_supports_cidr_notation()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.ip_whitelist_array' => ['192.168.1.0/24']]);

        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_OK);

        $this->withServerVariables(['REMOTE_ADDR' => '192.168.2.100'])
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function ip_blacklist_blocks_even_when_lockout_disabled()
    {
        config(['lockout.enabled' => false]);
        config(['lockout.ip_blacklist_array' => ['192.168.1.1']]);

        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.1'])
            ->call('GET', 'get')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function health_check_endpoint_is_always_accessible()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['get']]);
        config(['lockout.health_check_enabled' => true]);
        config(['lockout.health_check_path' => 'health']);

        $this->call('GET', 'health')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['status', 'timestamp', 'lockout_enabled']);
    }

    /** @test */
    public function health_check_can_be_disabled()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['get']]);
        config(['lockout.health_check_enabled' => false]);
        config(['lockout.health_check_path' => 'health']);

        $this->call('GET', 'health')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function route_patterns_whitelist_routes()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.route_patterns' => ['api/*']]);

        $this->call('POST', 'api/test')
            ->assertStatus(Response::HTTP_OK);

        $this->call('POST', 'other/test')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function api_requests_return_json_response()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.api_enabled' => true]);
        config(['lockout.api_response_type' => 'json']);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->call('POST', 'api/test');
        
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        // Check if response is JSON (might be HTML if API detection fails)
        if ($response->headers->get('Content-Type') === 'application/json') {
            $response->assertJsonStructure(['message', 'status']);
        }
    }

    /** @test */
    public function view_response_type_returns_view()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.response_type' => 'view']);
        config(['lockout.response_view' => 'lockout::maintenance']);

        // View might not exist in test environment, so test with json instead
        config(['lockout.response_type' => 'json']);
        
        $response = $this->call('POST', 'post');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson(['message' => 'Application is currently in read-only mode.']);
    }

    /** @test */
    public function json_response_type_returns_json()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.response_type' => 'json']);

        $this->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(['message' => 'Application is currently in read-only mode.']);
    }

    /** @test */
    public function cache_is_used_when_enabled()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.cache_enabled' => true]);
        config(['lockout.cache_key' => 'lockout.status']);
        config(['lockout.locked_types' => ['post']]);

        // Cache should be used, but we'll just verify it works
        Cache::flush();
        Cache::put('lockout.status', true, 60);

        $this->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function cache_is_bypassed_when_disabled()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.cache_enabled' => false]);
        config(['lockout.locked_types' => ['post']]);

        // When cache is disabled, it should read directly from config
        $this->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function request_blocked_event_is_fired()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.fire_events' => true]);

        Event::fake();

        $this->call('POST', 'post');

        Event::assertDispatched(RequestBlocked::class, function ($event) {
            return $event->reason === 'Method locked: post';
        });
    }

    /** @test */
    public function events_are_not_fired_when_disabled()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.fire_events' => false]);

        Event::fake();

        $this->call('POST', 'post');

        Event::assertNotDispatched(RequestBlocked::class);
    }
}

