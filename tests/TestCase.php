<?php

namespace Rappasoft\Lockout\Tests;

use Illuminate\Support\Facades\Artisan;
use Rappasoft\Lockout\LockoutServiceProvider;

/**
 * Class LockoutTest.
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [LockoutServiceProvider::class];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        app('config')->set('view.paths', [__DIR__.'/resources/views']);

        $app['router']->get('get', ['uses' => function () {
            return 'got';
        }]);

        $app['router']->post('post', ['uses' => function () {
            return 'posted';
        }]);

        $app['router']->post('login', ['uses' => function () {
            return 'logged in';
        }]);

        $app['router']->post('logout', ['uses' => function () {
            return 'logged out';
        }]);

        $app['router']->post('password/confirm', ['uses' => function () {
            return 'password confirmed';
        }]);

        $app['router']->put('put', ['uses' => function () {
            return 'placed';
        }]);

        $app['router']->patch('patch', ['uses' => function () {
            return 'patched';
        }]);

        $app['router']->delete('delete', ['uses' => function () {
            return 'deleted';
        }]);

        $app['router']->put('update-profile', ['uses' => function () {
            return 'profile updated';
        }]);

        $app['router']->get('blocked-page', ['uses' => function () {
            return 'blocked';
        }]);

        $app['router']->get('register', ['uses' => function () {
            return 'register';
        }]);

        $app['router']->get('subscribe', ['uses' => function () {
            return 'subscribe';
        }]);

        $app['router']->post('allowed-post', ['uses' => function () {
            return 'allowed post';
        }]);

        $app['router']->put('allowed-put', ['uses' => function () {
            return 'allowed put';
        }]);

        $app['router']->post('custom-login', ['uses' => function () {
            return 'custom logged in';
        }]);

        $app['router']->post('custom-logout', ['uses' => function () {
            return 'custom logged out';
        }]);

        $app['router']->post('other-path', ['uses' => function () {
            return 'other path';
        }]);

        $app['router']->get('blocked', ['uses' => function () {
            return 'blocked';
        }]);

        $app['router']->post('api/test', ['uses' => function () {
            return 'api test';
        }]);

        $app['router']->post('other/test', ['uses' => function () {
            return 'other test';
        }]);
    }

    /**
     * @param  $view
     * @param  array  $parameters
     * @return string
     */
    protected function renderView($view, $parameters = [])
    {
        Artisan::call('view:clear');

        if (is_string($view)) {
            $view = view($view)->with($parameters);
        }

        return trim((string) $view);
    }
}
