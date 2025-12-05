<?php

namespace Rappasoft\Lockout\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Rappasoft\Lockout\Events\RequestBlocked;
use Rappasoft\Lockout\Helpers\IpHelper;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class CheckForReadOnlyMode.
 */
class CheckForReadOnlyMode
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return SymfonyResponse
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        // Check if lockout is enabled (with caching)
        $enabled = $this->isLockoutEnabled();

        if (! $enabled) {
            // Even if lockout is disabled, check IP blacklist
            if ($this->isIpBlocked($request)) {
                return $this->blockRequest($request, 'IP blacklisted');
            }

            return $next($request);
        }

        $requestMethod = strtolower($request->method());
        $requestPath = $request->path();
        $clientIp = $request->ip();

        // Health check endpoint is always accessible
        if ($this->isHealthCheckPath($requestPath)) {
            return $next($request);
        }

        // Check IP whitelist first
        if ($this->isIpWhitelisted($clientIp)) {
            return $next($request);
        }

        // Check IP blacklist
        if ($this->isIpBlocked($clientIp)) {
            return $this->blockRequest($request, 'IP blacklisted');
        }

        // Check role-based exceptions
        if ($this->hasAllowedRole($request)) {
            return $next($request);
        }

        // Check route patterns
        if ($this->matchesRoutePattern($request)) {
            return $next($request);
        }

        // Check route names
        if ($this->matchesRouteName($request)) {
            return $next($request);
        }

        // Check to see if this method and path is whitelisted
        foreach (config('lockout.whitelist', []) as $method => $path) {
            if ($request->isMethod($method) && $requestPath === $path) {
                return $next($request);
            }
        }

        // Handle POST requests with allow_login enabled
        if ($requestMethod === 'post' && config('lockout.allow_login')) {
            $loginPath = config('lockout.login_path', 'login');
            $logoutPath = config('lockout.logout_path', 'logout');

            if ($requestPath === $loginPath || $requestPath === $logoutPath) {
                return $next($request);
            }
        }

        // Check if the request method is in the locked types
        $lockedTypes = array_map('strtolower', config('lockout.locked_types', []));
        if (in_array($requestMethod, $lockedTypes, true)) {
            return $this->blockRequest($request, 'Method locked: '.$requestMethod);
        }

        // Block any other specific get requests that may alter data
        if ($requestMethod === 'get') {
            $pages = config('lockout.pages', []);
            if (is_array($pages) && in_array($requestPath, $pages, true)) {
                return $this->blockRequest($request, 'Page blocked: '.$requestPath);
            }
        }

        return $next($request);
    }

    /**
     * Check if lockout is enabled (with caching).
     */
    protected function isLockoutEnabled(): bool
    {
        if (! config('lockout.cache_enabled', true)) {
            return (bool) config('lockout.enabled');
        }

        $cacheKey = config('lockout.cache_key', 'lockout.status');
        $cacheTtl = config('lockout.cache_ttl', 60);

        return Cache::remember($cacheKey, $cacheTtl, function () {
            return (bool) config('lockout.enabled');
        });
    }

    /**
     * Check if IP is whitelisted.
     */
    protected function isIpWhitelisted(string $ip): bool
    {
        $whitelistEnv = config('lockout.ip_whitelist', '');
        $whitelistArray = config('lockout.ip_whitelist_array', []);

        $whitelist = array_merge(
            IpHelper::parseIpList($whitelistEnv),
            $whitelistArray
        );

        return IpHelper::isIpAllowed($ip, $whitelist);
    }

    /**
     * Check if IP is blacklisted.
     */
    protected function isIpBlocked(string|Request $ipOrRequest): bool
    {
        $ip = $ipOrRequest instanceof Request ? $ipOrRequest->ip() : $ipOrRequest;

        $blacklistEnv = config('lockout.ip_blacklist', '');
        $blacklistArray = config('lockout.ip_blacklist_array', []);

        $blacklist = array_merge(
            IpHelper::parseIpList($blacklistEnv),
            $blacklistArray
        );

        return IpHelper::isIpBlocked($ip, $blacklist);
    }

    /**
     * Check if user has an allowed role.
     */
    protected function hasAllowedRole(Request $request): bool
    {
        $allowedRoles = config('lockout.allowed_roles', []);

        if (empty($allowedRoles)) {
            return false;
        }

        $user = $request->user();

        if (! $user) {
            return false;
        }

        // Check if user has any of the allowed roles
        // Supports both Laravel's built-in roles and common packages
        foreach ($allowedRoles as $role) {
            // Check for Laravel Bouncer, Spatie Permission, or similar packages
            if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
                return true;
            }

            // Check for simple role attribute
            if (isset($user->role) && $user->role === $role) {
                return true;
            }

            // Check for roles relationship
            if (method_exists($user, 'roles')) {
                $roles = $user->roles;
                if ($roles && $roles->contains('name', $role)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if request matches route patterns.
     */
    protected function matchesRoutePattern(Request $request): bool
    {
        $patterns = config('lockout.route_patterns', []);

        if (empty($patterns)) {
            return false;
        }

        $path = $request->path();

        foreach ($patterns as $pattern) {
            // Convert wildcard pattern to regex
            $regex = str_replace(['*', '/'], ['.*', '\/'], $pattern);
            $regex = '/^'.$regex.'$/';

            if (preg_match($regex, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if request matches route names.
     */
    protected function matchesRouteName(Request $request): bool
    {
        $routeNames = config('lockout.route_names', []);

        if (empty($routeNames)) {
            return false;
        }

        $route = Route::current();

        if (! $route) {
            return false;
        }

        $routeName = $route->getName();

        return $routeName && in_array($routeName, $routeNames, true);
    }

    /**
     * Check if path is the health check endpoint.
     */
    protected function isHealthCheckPath(string $path): bool
    {
        if (! config('lockout.health_check_enabled', true)) {
            return false;
        }

        $healthCheckPath = config('lockout.health_check_path', 'health');

        return $path === $healthCheckPath;
    }

    /**
     * Block the request and return appropriate response.
     */
    protected function blockRequest(Request $request, string $reason): SymfonyResponse
    {
        // Fire event if enabled
        if (config('lockout.fire_events', true)) {
            event(new RequestBlocked($request, $reason));
        }

        // Check if this is an API request
        if ($this->isApiRequest($request)) {
            return $this->handleApiResponse($request);
        }

        // Handle custom response types
        $responseType = config('lockout.response_type', 'abort');

        return match ($responseType) {
            'view' => $this->handleViewResponse($request),
            'json' => $this->handleJsonResponse($request),
            default => $this->handleAbortResponse($request),
        };
    }

    /**
     * Check if request is an API request.
     */
    protected function isApiRequest(Request $request): bool
    {
        // Check if API is enabled
        if (! config('lockout.api_enabled', true)) {
            return false;
        }

        // Check if request expects JSON
        if ($request->expectsJson()) {
            return true;
        }

        // Check if path starts with api/
        if (str_starts_with($request->path(), 'api/')) {
            return true;
        }

        // Check if route is in api middleware group
        $route = Route::current();
        if ($route && in_array('api', $route->middleware(), true)) {
            return true;
        }

        return false;
    }

    /**
     * Handle API response.
     */
    protected function handleApiResponse(Request $request): SymfonyResponse
    {
        $apiResponseType = config('lockout.api_response_type', 'json');
        $message = config('lockout.api_response_message', [
            'message' => 'Application is currently in read-only mode.',
            'status' => 'maintenance',
        ]);

        if ($apiResponseType === 'json') {
            return response()->json($message, config('lockout.response_code', Response::HTTP_UNAUTHORIZED));
        }

        return $this->handleAbortResponse($request);
    }

    /**
     * Handle view response.
     */
    protected function handleViewResponse(Request $request): SymfonyResponse
    {
        $view = config('lockout.response_view', 'lockout::maintenance');
        $message = config('lockout.response_message', 'Application is currently in read-only mode.');
        $code = config('lockout.response_code', Response::HTTP_UNAUTHORIZED);

        return response()->view($view, ['message' => $message], $code);
    }

    /**
     * Handle JSON response.
     */
    protected function handleJsonResponse(Request $request): SymfonyResponse
    {
        $message = config('lockout.response_message', 'Application is currently in read-only mode.');
        $code = config('lockout.response_code', Response::HTTP_UNAUTHORIZED);

        return response()->json(['message' => $message], $code);
    }

    /**
     * Handle abort response (default).
     */
    protected function handleAbortResponse(Request $request): SymfonyResponse
    {
        $code = config('lockout.response_code', Response::HTTP_UNAUTHORIZED);
        $message = config('lockout.response_message', 'Application is currently in read-only mode.');

        return response($message, $code);
    }
}
