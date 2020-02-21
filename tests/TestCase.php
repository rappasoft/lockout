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
     *
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

        $app['router']->put('put', ['uses' => function () {
            return 'placed';
        }]);

        $app['router']->patch('patch', ['uses' => function () {
            return 'patched';
        }]);

        $app['router']->delete('delete', ['uses' => function () {
            return 'deleted';
        }]);
    }

    /**
     * @param $view
     * @param  array  $parameters
     *
     * @return string
     */
    protected function renderView($view, $parameters = [])
    {
        Artisan::call('view:clear');

        if (is_string($view)) {
            $view = view($view)->with($parameters);
        }

        return trim((string) ($view));
    }
}
