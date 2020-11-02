![Package Logo](https://banners.beyondco.de/Laravel%20Lockout.png?theme=light&packageName=rappasoft%2Flockout&pattern=hideout&style=style_1&description=Put+your+Laravel+application+into+read-only+mode&md=1&fontSize=100px&images=lock-closed)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rappasoft/lockout.svg?style=flat-square)](https://packagist.org/packages/rappasoft/lockout)
![Run Tests](https://github.com/rappasoft/lockout/workflows/Run%20Tests/badge.svg?branch=master)
[![StyleCI](https://styleci.io/repos/242222088/shield?style=plastic)](https://github.styleci.io/repos/242222088)
[![Code Coverage](https://scrutinizer-ci.com/g/rappasoft/lockout/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/rappasoft/lockout/?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/rappasoft/lockout.svg?style=flat-square)](https://scrutinizer-ci.com/g/rappasoft/lockout)
[![Total Downloads](https://img.shields.io/packagist/dt/rappasoft/lockout.svg?style=flat-square)](https://packagist.org/packages/rappasoft/lockout)

A simple .env flag that can place your application into read-only mode. By default only get requests are allowed. You can optionally allow authentication.

## Installation

You can install the package via composer:

``` bash
composer require rappasoft/lockout
```

## Version Compatibility

 Laravel  | Lockout
:---------|:----------
 6.x      | 1.x
 7.x      | 2.x
 8.x      | 3.x

## Publish

You can publish the configuration file to override the defaults:

``` bash
php artisan vendor:publish --provider="Rappasoft\Lockout\LockoutServiceProvider"
```

## Usage

### Enable

Add to .env file

``` bash
APP_READ_ONLY = true
```

By default only pages accessed with GET requests will be allowed, everything else will throw a 401 Unauthorized response. You can modify the blocked request types in the lockout configuration file.

### Configure

To optionally allow logging into the application to view secure areas, after login only GET requests will still be allowed.

``` bash
APP_READ_ONLY_LOGIN = true
```

You can set your login/logout paths from the configuration file if they differ from the Laravel default.

#### Locking GET Pages

If you want to block access to regular pages in addition to the lockout types, or just by themselves you can edit the pages array of the config file:

``` php
'pages' => [
    'register', // Blocks access to the register page
],
```

#### Conditionally Rendering Views

You may conditionally render views based on the status of the lockout with this blade helper:

``` php
@readonly
    Sorry for the inconvenience, but our application is currently in read-only mode. Please check back soon.
@endreadonly
```

#### Whitelisting Pages

You may whitelist certain paths for certain methods. I.e. part of your application is behind the password.confirm middleware and you want the demo user to be able to access it.

```
'whitelist' => [
    'post' => '/password/confirm',
],
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email rappa819@gmail.com instead of using the issue tracker.

## Credits

- [Anthony Rappa](https://github.com/rappasoft)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
