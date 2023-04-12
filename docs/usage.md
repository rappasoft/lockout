---
title: Usage
weight: 7
---

## Enable

Add to .env file

``` bash
APP_READ_ONLY = true
```

By default only pages accessed with GET requests will be allowed, everything else will throw a 401 Unauthorized response. You can modify the blocked request types in the lockout configuration file.

## Configure

To optionally allow logging into the application to view secure areas, after login only GET requests will still be allowed.

``` bash
APP_READ_ONLY_LOGIN = true
```

You can set your login/logout paths from the configuration file if they differ from the Laravel default.

### Locking GET Pages

If you want to block access to regular pages in addition to the lockout types, or just by themselves you can edit the pages array of the config file:

``` php
'pages' => [
    'register', // Blocks access to the register page
],
```

### Conditionally Rendering Views

You may conditionally render views based on the status of the lockout with this blade helper:

``` php
@readonly
    Sorry for the inconvenience, but our application is currently in read-only mode. Please check back soon.
@endreadonly
```

### Whitelisting Pages

You may whitelist certain paths for certain methods. I.e. part of your application is behind the password.confirm middleware and you want the demo user to be able to access it.

```
'whitelist' => [
    'post' => '/password/confirm',
],
```
