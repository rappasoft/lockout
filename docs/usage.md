---
title: Usage
weight: 7
---

## Quick Start

### Enable Lockout

Add to your `.env` file:

```bash
APP_READ_ONLY=true
```

By default, only pages accessed with GET requests will be allowed. All other request types (POST, PUT, PATCH, DELETE) will be blocked with a 401 Unauthorized response.

### Disable Lockout

```bash
APP_READ_ONLY=false
```

Or use the Artisan command:

```bash
php artisan lockout:disable
```

## Basic Configuration

### Allow Login During Lockout

To allow users to log in and view secure areas (but still only allow GET requests after login):

```bash
APP_READ_ONLY_LOGIN=true
```

You can customize your login/logout paths in the configuration file if they differ from Laravel defaults.

### Locking Specific GET Pages

Block access to specific pages even for GET requests:

```php
'pages' => [
    'register',
    'subscribe',
    'admin/settings',
],
```

### Whitelisting Specific Routes

Allow specific routes to bypass lockout:

```php
'whitelist' => [
    'post' => 'password/confirm',
    'put' => 'profile/update',
],
```

## Advanced Features

### IP Whitelist/Blacklist

#### IP Whitelist

Allow specific IP addresses to bypass lockout completely:

**Via .env:**
```bash
LOCKOUT_IP_WHITELIST=127.0.0.1,192.168.1.100
```

**Via Config:**
```php
'ip_whitelist_array' => [
    '127.0.0.1',
    '192.168.1.0/24',  // CIDR notation supported
    '10.0.0.0/8',
],
```

#### IP Blacklist

Block specific IP addresses even when lockout is disabled:

**Via .env:**
```bash
LOCKOUT_IP_BLACKLIST=192.168.1.50
```

**Via Config:**
```php
'ip_blacklist_array' => [
    '192.168.1.50',
    '10.0.0.100/24',
],
```

### Role-Based Exceptions

Allow users with specific roles to bypass lockout:

```php
'allowed_roles' => [
    'admin',
    'super-admin',
    'maintenance',
],
```

The package supports:
- Laravel Bouncer
- Spatie Permission
- Simple role attributes (`$user->role`)
- Roles relationships

### Custom Response Types

#### View Response

Show a custom maintenance page:

```php
'response_type' => 'view',
'response_view' => 'lockout::maintenance',
'response_message' => 'We are currently performing maintenance.',
'response_code' => 503,
```

Publish the view to customize:

```bash
php artisan vendor:publish --tag=lockout-views
```

#### JSON Response

Return JSON for API requests:

```php
'response_type' => 'json',
'response_code' => 503,
```

#### API-Specific Handling

Automatically detect and handle API requests differently:

```php
'api_enabled' => true,
'api_response_type' => 'json',
'api_response_message' => [
    'message' => 'Application is in maintenance mode.',
    'status' => 'maintenance',
],
```

### Route Patterns and Names

#### Route Patterns

Whitelist routes by pattern:

```php
'route_patterns' => [
    'api/*',           // All API routes
    'health',          // Health check
    'admin/*',         // All admin routes
],
```

#### Route Names

Whitelist routes by name:

```php
'route_names' => [
    'health.check',
    'api.status',
    'admin.dashboard',
],
```

### Health Check Endpoint

A health check endpoint is automatically registered at `/health` (configurable):

```bash
GET /health
```

Response:
```json
{
    "status": "ok",
    "timestamp": "2025-01-15T10:30:00Z",
    "lockout_enabled": true
}
```

Disable or customize:

```php
'health_check_enabled' => true,
'health_check_path' => 'health',
```

### Cache Integration

Improve performance by caching lockout status:

```php
'cache_enabled' => true,
'cache_key' => 'lockout.status',
'cache_ttl' => 60,  // seconds
```

Disable caching:

```php
'cache_enabled' => false,
```

Clear cache manually:

```bash
php artisan cache:clear
```

Or when enabling/disabling:

```bash
php artisan lockout:enable --clear-cache
php artisan lockout:disable --clear-cache
```

### Event System

The package fires events that you can listen to:

#### Available Events

- `Rappasoft\Lockout\Events\LockoutEnabled` - Fired when lockout is enabled
- `Rappasoft\Lockout\Events\LockoutDisabled` - Fired when lockout is disabled
- `Rappasoft\Lockout\Events\RequestBlocked` - Fired when a request is blocked

#### Example Listener

```php
use Rappasoft\Lockout\Events\RequestBlocked;
use Illuminate\Support\Facades\Log;

class LogBlockedRequests
{
    public function handle(RequestBlocked $event)
    {
        Log::warning('Request blocked', [
            'path' => $event->request->path(),
            'method' => $event->request->method(),
            'ip' => $event->request->ip(),
            'reason' => $event->reason,
        ]);
    }
}
```

Register in `EventServiceProvider`:

```php
protected $listen = [
    RequestBlocked::class => [
        LogBlockedRequests::class,
    ],
];
```

Disable events:

```php
'fire_events' => false,
```

## Artisan Commands

### Enable Lockout

```bash
php artisan lockout:enable
```

Options:
- `--clear-cache` - Clear the lockout cache after enabling

### Disable Lockout

```bash
php artisan lockout:disable
```

Options:
- `--clear-cache` - Clear the lockout cache after disabling

### Check Status

```bash
php artisan lockout:status
```

Output:
```
Lockout Status:
─────────────────
Enabled: Yes
Cached: Yes

Configuration:
  Allow Login: Yes
  Locked Types: post, put, patch, delete
  Response Type: abort
  Health Check: Yes
  Cache Enabled: Yes
```

## Blade Directive

Conditionally render content based on lockout status:

```blade
@readonly
    <div class="alert alert-warning">
        Application is currently in read-only mode.
    </div>
@else
    <div class="alert alert-info">
        Application is fully operational.
    </div>
@endreadonly
```

## Best Practices

1. **Use IP Whitelist for Maintenance**: Whitelist your IP before enabling lockout to ensure you can still access the application.

2. **Use Health Check for Monitoring**: Configure your monitoring tools to check the `/health` endpoint.

3. **Enable Caching in Production**: Improve performance by enabling cache for lockout status.

4. **Use Events for Logging**: Listen to `RequestBlocked` events to track blocked requests.

5. **Customize Response for Better UX**: Use view responses for web requests and JSON for API requests.

6. **Test Before Production**: Always test lockout in a staging environment first.
