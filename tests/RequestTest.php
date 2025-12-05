<?php

namespace Rappasoft\Lockout\Tests;

use Illuminate\Http\Response;

/**
 * Class RequestTest.
 */
class RequestTest extends TestCase
{
    /** @test */
    public function get_requests_can_be_accessed_with_the_plugin_off()
    {
        config(['lockout.enabled' => false]);

        $crawler = $this->call('GET', 'get')
            ->assertStatus(Response::HTTP_OK);

        $this->assertEquals('got', $crawler->getContent());
    }

    /** @test */
    public function get_requests_cannot_be_accessed_with_the_plugin_on()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['get']]);

        $this->call('GET', 'get')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function post_requests_can_be_accessed_with_the_plugin_off()
    {
        config(['lockout.enabled' => false]);

        $crawler = $this->call('POST', 'post')
            ->assertStatus(Response::HTTP_OK);

        $this->assertEquals('posted', $crawler->getContent());
    }

    /** @test */
    public function post_requests_cannot_be_accessed_with_the_plugin_on()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);

        $this->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function put_requests_can_be_accessed_with_the_plugin_off()
    {
        config(['lockout.enabled' => false]);

        $crawler = $this->call('PUT', 'put')
            ->assertStatus(Response::HTTP_OK);

        $this->assertEquals('placed', $crawler->getContent());
    }

    /** @test */
    public function put_requests_cannot_be_accessed_with_the_plugin_on()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['put']]);

        $this->call('PUT', 'put')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function patch_requests_can_be_accessed_with_the_plugin_off()
    {
        config(['lockout.enabled' => false]);

        $crawler = $this->call('PATCH', 'patch')
            ->assertStatus(Response::HTTP_OK);

        $this->assertEquals('patched', $crawler->getContent());
    }

    /** @test */
    public function patch_requests_cannot_be_accessed_with_the_plugin_on()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['patch']]);

        $this->call('PATCH', 'patch')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function delete_requests_can_be_accessed_with_the_plugin_off()
    {
        config(['lockout.enabled' => false]);

        $crawler = $this->call('DELETE', 'delete')
            ->assertStatus(Response::HTTP_OK);

        $this->assertEquals('deleted', $crawler->getContent());
    }

    /** @test */
    public function deleted_requests_cannot_be_accessed_with_the_plugin_on()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['delete']]);

        $this->call('DELETE', 'delete')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_user_can_login_and_logout_with_override_on()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.allow_login' => true]);
        config(['lockout.locked_types' => ['post']]);

        $crawler = $this->call('POST', 'login')
            ->assertStatus(Response::HTTP_OK);

        $this->assertEquals('logged in', $crawler->getContent());

        $crawler = $this->call('POST', 'logout')
            ->assertStatus(Response::HTTP_OK);

        $this->assertEquals('logged out', $crawler->getContent());
    }

    public function test_lock_certain_get_pages()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.pages' => ['get']]);
        config(['lockout.locked_types' => []]);

        $this->call('GET', 'get')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_a_page_that_is_whitelisted_is_allowed()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.whitelist' => [
            'post' => 'password/confirm',
        ]]);

        $this->call('POST', 'password/confirm')
            ->assertStatus(Response::HTTP_OK);

        config(['lockout.whitelist' => [
            'post' => 'password/confirm/123',
        ]]);

        $this->call('POST', 'password/confirm')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function multiple_whitelist_entries_work_correctly()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post', 'put']]);
        config(['lockout.whitelist' => [
            'post' => 'password/confirm',
            'put' => 'update-profile',
        ]]);

        $this->call('POST', 'password/confirm')
            ->assertStatus(Response::HTTP_OK);

        $this->call('PUT', 'update-profile')
            ->assertStatus(Response::HTTP_OK);

        $this->call('POST', 'other-path')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function post_requests_to_non_login_paths_are_blocked_when_allow_login_is_true()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.allow_login' => true]);
        config(['lockout.locked_types' => ['post']]);

        $this->call('POST', 'login')
            ->assertStatus(Response::HTTP_OK);

        $this->call('POST', 'logout')
            ->assertStatus(Response::HTTP_OK);

        $this->call('POST', 'other-path')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function post_requests_are_blocked_when_allow_login_is_false()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.allow_login' => false]);
        config(['lockout.locked_types' => ['post']]);

        $this->call('POST', 'login')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->call('POST', 'logout')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function get_requests_not_in_pages_array_are_allowed()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.pages' => ['blocked-page']]);
        config(['lockout.locked_types' => []]);

        $this->call('GET', 'get')
            ->assertStatus(Response::HTTP_OK);

        $this->call('GET', 'blocked-page')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function multiple_pages_in_pages_array_are_blocked()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.pages' => ['register', 'subscribe']]);
        config(['lockout.locked_types' => []]);

        $this->call('GET', 'register')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->call('GET', 'subscribe')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->call('GET', 'get')
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function empty_whitelist_does_not_break_middleware()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.whitelist' => []]);
        config(['lockout.locked_types' => ['post']]);

        $this->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function empty_locked_types_allows_all_methods_except_pages()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => []]);
        config(['lockout.pages' => []]);

        $this->call('POST', 'post')
            ->assertStatus(Response::HTTP_OK);

        $this->call('PUT', 'put')
            ->assertStatus(Response::HTTP_OK);

        $this->call('DELETE', 'delete')
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function whitelist_takes_precedence_over_locked_types()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post', 'put', 'delete']]);
        config(['lockout.whitelist' => [
            'post' => 'allowed-post',
            'put' => 'allowed-put',
        ]]);

        $this->call('POST', 'allowed-post')
            ->assertStatus(Response::HTTP_OK);

        $this->call('PUT', 'allowed-put')
            ->assertStatus(Response::HTTP_OK);

        $this->call('POST', 'other-post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->call('DELETE', 'delete')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function custom_login_and_logout_paths_work()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.allow_login' => true]);
        config(['lockout.login_path' => 'custom-login']);
        config(['lockout.logout_path' => 'custom-logout']);
        config(['lockout.locked_types' => ['post']]);

        $this->call('POST', 'custom-login')
            ->assertStatus(Response::HTTP_OK);

        $this->call('POST', 'custom-logout')
            ->assertStatus(Response::HTTP_OK);

        $this->call('POST', 'login')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->call('POST', 'logout')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function get_requests_work_when_not_in_pages_array_and_locked_types_empty()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => []]);
        config(['lockout.pages' => ['blocked']]);

        $this->call('GET', 'get')
            ->assertStatus(Response::HTTP_OK);

        $this->call('GET', 'blocked')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function pages_array_with_non_array_value_is_handled_gracefully()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => []]);
        config(['lockout.pages' => null]);

        $this->call('GET', 'get')
            ->assertStatus(Response::HTTP_OK);
    }
}
