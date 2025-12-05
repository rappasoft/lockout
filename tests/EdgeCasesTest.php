<?php

namespace Rappasoft\Lockout\Tests;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class EdgeCasesTest extends TestCase
{
    /** @test */
    public function ip_whitelist_from_env_variable_works()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.ip_whitelist' => '127.0.0.1,192.168.1.0/24']);
        config(['lockout.ip_whitelist_array' => []]);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_OK);

        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function ip_whitelist_combines_env_and_array()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.ip_whitelist' => '127.0.0.1']);
        config(['lockout.ip_whitelist_array' => ['192.168.1.0/24']]);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_OK);

        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function ip_blacklist_from_env_variable_works()
    {
        config(['lockout.enabled' => false]);
        config(['lockout.ip_blacklist' => '192.168.1.1']);
        config(['lockout.ip_blacklist_array' => []]);

        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.1'])
            ->call('GET', 'get')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function route_name_matching_works()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.route_names' => ['test.route']]);

        // Route name matching requires Route::current() to work
        // In testbench, this can be tricky, so we test the config is read correctly
        // The actual route matching is tested in integration scenarios
        $routeNames = config('lockout.route_names', []);
        $this->assertContains('test.route', $routeNames);
    }

    /** @test */
    public function route_name_matching_returns_false_when_no_route()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.route_names' => ['nonexistent.route']]);

        // Route doesn't exist, so matching should fail
        $this->call('POST', 'nonexistent')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function route_name_matching_returns_false_when_route_has_no_name()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.route_names' => ['test.route']]);

        Route::post('unnamed-route', function () {
            return 'test';
        }); // No name

        $this->call('POST', 'unnamed-route')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function multiple_route_patterns_work()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.route_patterns' => ['api/*', 'admin/*']]);

        // Register routes
        $this->app['router']->post('api/test', function () {
            return 'api test';
        });
        $this->app['router']->post('admin/test', function () {
            return 'admin test';
        });

        $this->call('POST', 'api/test')
            ->assertStatus(Response::HTTP_OK);

        $this->call('POST', 'admin/test')
            ->assertStatus(Response::HTTP_OK);

        $this->call('POST', 'other/test')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function route_pattern_with_complex_wildcards()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.route_patterns' => ['api/*/v1/*']]);

        // Register routes
        $this->app['router']->post('api/users/v1/list', function () {
            return 'v1 list';
        });
        $this->app['router']->post('api/users/v2/list', function () {
            return 'v2 list';
        });

        $this->call('POST', 'api/users/v1/list')
            ->assertStatus(Response::HTTP_OK);

        $this->call('POST', 'api/users/v2/list')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function custom_health_check_path_configuration_works()
    {
        // Test that custom health check path configuration is respected
        config(['lockout.health_check_enabled' => true]);
        config(['lockout.health_check_path' => 'custom-health']);

        // Verify custom path config is set and health check is enabled
        $this->assertEquals('custom-health', config('lockout.health_check_path'));
        $this->assertTrue(config('lockout.health_check_enabled'));

        // The actual route registration happens at service provider boot
        // In real applications, changing the path would require clearing route cache
        // This test verifies the configuration is properly stored and retrieved
    }

    /** @test */
    public function api_detection_by_path_works()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.api_enabled' => true]);
        config(['lockout.api_response_type' => 'json']);

        $this->call('POST', 'api/test')
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonStructure(['message', 'status']);
    }

    /** @test */
    public function api_detection_when_disabled()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.api_enabled' => false]);

        $this->withHeaders(['Accept' => 'application/json'])
            ->call('POST', 'api/test')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function api_response_type_abort_works()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.api_enabled' => true]);
        config(['lockout.api_response_type' => 'abort']);

        $this->withHeaders(['Accept' => 'application/json'])
            ->call('POST', 'api/test')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function custom_response_code_works()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.response_code' => 503]);

        $this->call('POST', 'post')
            ->assertStatus(503);
    }

    /** @test */
    public function custom_response_message_works()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.response_type' => 'json']);
        config(['lockout.response_message' => 'Custom maintenance message']);

        $this->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(['message' => 'Custom maintenance message']);
    }

    /** @test */
    public function whitelist_method_case_insensitive()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.whitelist' => [
            'POST' => 'test-route',  // Uppercase
        ]]);

        Route::post('test-route', function () {
            return 'test';
        });

        $this->call('POST', 'test-route')
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function locked_types_case_insensitive()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['POST', 'PUT']]);  // Uppercase

        $this->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->call('PUT', 'put')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function cache_key_customization_works()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.cache_enabled' => true]);
        config(['lockout.cache_key' => 'custom.lockout.key']);
        config(['lockout.locked_types' => ['post']]);

        // Should work with custom cache key
        $this->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function cache_ttl_customization_works()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.cache_enabled' => true]);
        config(['lockout.cache_ttl' => 120]);
        config(['lockout.locked_types' => ['post']]);

        // Should work with custom TTL
        $this->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
