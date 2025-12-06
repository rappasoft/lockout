---
title: Configuration
weight: 6
---

## Publishing Configuration

Publish the configuration file to customize all settings:

```bash
php artisan vendor:publish --provider="Rappasoft\Lockout\LockoutServiceProvider" --tag=config
```

This will create `config/lockout.php` in your application.

## Configuration Options

### Basic Settings

#### `enabled`
Enable or disable lockout mode.

**Default:** `env('APP_READ_ONLY', false)`

```php
'enabled' => env('APP_READ_ONLY', false),
```

#### `allow_login`
Allow users to log in during lockout (only GET requests allowed after login).

**Default:** `env('APP_READ_ONLY_LOGIN', false)`

```php
'allow_login' => env('APP_READ_ONLY_LOGIN', false),
```

#### `login_path` / `logout_path`
Customize login and logout paths.

**Default:** `'login'` / `'logout'`

```php
'login_path' => 'login',
'logout_path' => 'logout',
```

### Request Type Configuration

#### `locked_types`
HTTP methods to block when lockout is enabled.

**Default:** `['post', 'put', 'patch', 'delete']`

```php
'locked_types' => [
    'post',
    'put',
    'patch',
    'delete',
],
```

#### `pages`
Specific GET request paths to block.

**Default:** `[]`

```php
'pages' => [
    'register',
    'subscribe',
],
```

#### `whitelist`
Whitelist specific method/path combinations.

**Default:** `[]`

```php
'whitelist' => [
    'post' => 'password/confirm',
    'put' => 'profile/update',
],
```

### IP Management

#### `ip_whitelist`
Comma-separated list of IP addresses to whitelist (from .env).

**Default:** `env('LOCKOUT_IP_WHITELIST', '')`

```php
'ip_whitelist' => env('LOCKOUT_IP_WHITELIST', ''),
```

#### `ip_whitelist_array`
Array of IP addresses or CIDR ranges to whitelist.

**Default:** `[]`

```php
'ip_whitelist_array' => [
    '127.0.0.1',
    '192.168.1.0/24',
    '10.0.0.0/8',
],
```

#### `ip_blacklist`
Comma-separated list of IP addresses to blacklist (from .env).

**Default:** `env('LOCKOUT_IP_BLACKLIST', '')`

```php
'ip_blacklist' => env('LOCKOUT_IP_BLACKLIST', ''),
```

#### `ip_blacklist_array`
Array of IP addresses or CIDR ranges to blacklist.

**Default:** `[]`

```php
'ip_blacklist_array' => [
    '192.168.1.50',
    '10.0.0.100/24',
],
```

### Role-Based Access

#### `allowed_roles`
User roles that can bypass lockout.

**Default:** `[]`

```php
'allowed_roles' => [
    'admin',
    'super-admin',
],
```

### Response Configuration

#### `response_type`
Type of response when request is blocked.

**Options:** `'abort'`, `'view'`, `'json'`

**Default:** `env('LOCKOUT_RESPONSE_TYPE', 'abort')`

```php
'response_type' => env('LOCKOUT_RESPONSE_TYPE', 'abort'),
```

#### `response_view`
Blade view to render when `response_type` is `'view'`.

**Default:** `'lockout::maintenance'`

```php
'response_view' => 'lockout::maintenance',
```

#### `response_message`
Message to display in responses.

**Default:** `'Application is currently in read-only mode.'`

```php
'response_message' => 'Application is currently in read-only mode.',
```

#### `response_code`
HTTP status code for blocked requests.

**Default:** `401` (HTTP_UNAUTHORIZED)

```php
'response_code' => 401,  // Use 503 for maintenance mode
```

### Route Configuration

#### `route_patterns`
Route patterns to whitelist (supports wildcards).

**Default:** `[]`

```php
'route_patterns' => [
    'api/*',
    'health',
],
```

#### `route_names`
Route names to whitelist.

**Default:** `[]`

```php
'route_names' => [
    'health.check',
    'api.status',
],
```

### API Configuration

#### `api_enabled`
Enable API-specific handling.

**Default:** `env('LOCKOUT_API_ENABLED', true)`

```php
'api_enabled' => env('LOCKOUT_API_ENABLED', true),
```

#### `api_response_type`
Response type for API requests.

**Options:** `'json'`, `'abort'`

**Default:** `'json'`

```php
'api_response_type' => 'json',
```

#### `api_response_message`
JSON response message for API requests.

**Default:**
```php
'api_response_message' => [
    'message' => 'Application is currently in read-only mode.',
    'status' => 'maintenance',
],
```

### Health Check

#### `health_check_enabled`
Enable the health check endpoint.

**Default:** `env('LOCKOUT_HEALTH_CHECK_ENABLED', true)`

```php
'health_check_enabled' => env('LOCKOUT_HEALTH_CHECK_ENABLED', true),
```

#### `health_check_path`
Path for the health check endpoint.

**Default:** `env('LOCKOUT_HEALTH_CHECK_PATH', 'health')`

```php
'health_check_path' => env('LOCKOUT_HEALTH_CHECK_PATH', 'health'),
```

### Cache Configuration

#### `cache_enabled`
Enable caching of lockout status.

**Default:** `env('LOCKOUT_CACHE_ENABLED', true)`

```php
'cache_enabled' => env('LOCKOUT_CACHE_ENABLED', true),
```

#### `cache_key`
Cache key for lockout status.

**Default:** `'lockout.status'`

```php
'cache_key' => 'lockout.status',
```

#### `cache_ttl`
Cache time-to-live in seconds.

**Default:** `60`

```php
'cache_ttl' => 60,
```

### Event Configuration

#### `fire_events`
Enable event firing for lockout actions.

**Default:** `env('LOCKOUT_FIRE_EVENTS', true)`

```php
'fire_events' => env('LOCKOUT_FIRE_EVENTS', true),
```

## Environment Variables

All configuration can be set via environment variables:

```bash
# Basic
APP_READ_ONLY=true
APP_READ_ONLY_LOGIN=false

# IP Management
LOCKOUT_IP_WHITELIST=127.0.0.1,192.168.1.0/24
LOCKOUT_IP_BLACKLIST=192.168.1.50

# Response
LOCKOUT_RESPONSE_TYPE=view
LOCKOUT_RESPONSE_CODE=503

# API
LOCKOUT_API_ENABLED=true

# Health Check
LOCKOUT_HEALTH_CHECK_ENABLED=true
LOCKOUT_HEALTH_CHECK_PATH=health

# Cache
LOCKOUT_CACHE_ENABLED=true

# Events
LOCKOUT_FIRE_EVENTS=true
```

## Configuration Examples

### Maintenance Mode Setup

```php
'enabled' => true,
'response_type' => 'view',
'response_code' => 503,
'ip_whitelist_array' => [
    '127.0.0.1',  // Your IP
    '10.0.0.0/8',  // Internal network
],
'health_check_enabled' => true,
'cache_enabled' => true,
```

### API-Only Lockout

```php
'enabled' => true,
'api_enabled' => true,
'api_response_type' => 'json',
'route_patterns' => [
    'web/*',  // Allow web routes
],
'locked_types' => ['post', 'put', 'patch', 'delete'],
```

### Role-Based Maintenance

```php
'enabled' => true,
'allowed_roles' => [
    'admin',
    'maintenance',
],
'response_type' => 'view',
'response_code' => 503,
```
