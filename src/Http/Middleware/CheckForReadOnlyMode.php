<?php

namespace Rappasoft\Lockout\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

/**
 * Class CheckForReadOnlyMode.
 */
class CheckForReadOnlyMode
{
    /**
     * @param $request
     * @param  Closure  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (config('lockout.enabled')) {
            // Check to see if this method and path is whitelisted
            foreach (config('lockout.whitelist') as $method => $path) {
                if ($request->isMethod($method) && $request->path() === $path) {
                    return $next($request);
                }
            }

            foreach (config('lockout.locked_types', []) as $type) {
                if ($request->isMethod('post') && config('lockout.allow_login')) {
                    abort_if($request->path() !== config('lockout.login_path') && $request->path() !== config('lockout.logout_path'), Response::HTTP_UNAUTHORIZED);
                } elseif ($request->isMethod(strtolower($type))) {
                    abort(Response::HTTP_UNAUTHORIZED);
                }
            }

            // Block any other specific get requests that may alter data
            if ($request->isMethod('get')) {
                collect(config('lockout.pages', []))
                    ->each(function ($item) use ($request) {
                        if ($request->path() === $item) {
                            abort(Response::HTTP_UNAUTHORIZED);
                        }
                    });
            }
        }

        return $next($request);
    }
}
