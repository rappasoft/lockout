# Put your Laravel application into read-only mode

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rappasoft/lockout.svg?style=flat-square)](https://packagist.org/packages/rappasoft/lockout)
[![Quality Score](https://img.shields.io/scrutinizer/g/rappasoft/lockout.svg?style=flat-square)](https://scrutinizer-ci.com/g/rappasoft/lockout)
[![Total Downloads](https://img.shields.io/packagist/dt/rappasoft/lockout.svg?style=flat-square)](https://packagist.org/packages/rappasoft/lockout)

A simple .env flag that can place your application into read-only mode. By default only get requests are allowed. You can optionally allow authentication.

## Installation

You can install the package via composer:

``` bash
composer require rappasoft/lockout
```

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
