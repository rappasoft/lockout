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
        config(['lockout.pages' => 'get']);
        config(['lockout.locked_types' => []]);

        $this->call('GET', 'get')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_a_page_that_is_whitelisted_is_allowed()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.whitelist' => [
            'post' => 'password/confirm'
        ]]);

        $this->call('POST', 'password/confirm')
            ->assertStatus(Response::HTTP_OK);

        config(['lockout.whitelist' => [
            'post' => 'password/confirm/123'
        ]]);

        $this->call('POST', 'password/confirm')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
