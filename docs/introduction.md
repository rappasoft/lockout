---
title: Introduction
weight: 1
---

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rappasoft/lockout.svg?style=flat-square)](https://packagist.org/packages/rappasoft/lockout)
![Run Tests](https://github.com/rappasoft/lockout/workflows/Run%20Tests/badge.svg?branch=master)
[![StyleCI](https://styleci.io/repos/242222088/shield?style=plastic)](https://github.styleci.io/repos/242222088)
[![Code Coverage](https://scrutinizer-ci.com/g/rappasoft/lockout/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/rappasoft/lockout/?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/rappasoft/lockout.svg?style=flat-square)](https://scrutinizer-ci.com/g/rappasoft/lockout)
[![Total Downloads](https://img.shields.io/packagist/dt/rappasoft/lockout.svg?style=flat-square)](https://packagist.org/packages/rappasoft/lockout)

A powerful Laravel package that places your application into read-only mode with advanced features for maintenance, security, and access control.

## Features

- **Simple Configuration**: Enable/disable with a single `.env` flag
- **IP Whitelist/Blacklist**: Control access by IP address with CIDR support
- **Role-Based Exceptions**: Allow specific user roles to bypass lockout
- **Custom Responses**: View, JSON, or custom response types
- **API Support**: Automatic API detection with JSON responses
- **Route Patterns**: Whitelist routes by pattern or name
- **Health Check Endpoint**: Built-in monitoring endpoint
- **Cache Integration**: Performance optimization with configurable caching
- **Event System**: Listen to lockout events for logging and monitoring
- **Artisan Commands**: Manage lockout via command line
- **Blade Directives**: Conditional rendering based on lockout status

## Quick Start

1. Install the package:
```bash
composer require rappasoft/lockout
```

2. Enable lockout:
```bash
# In .env
APP_READ_ONLY=true
```

3. That's it! Your application is now in read-only mode.

## Use Cases

- **Maintenance Mode**: Put your application in maintenance while keeping it accessible
- **Emergency Lockdown**: Quickly disable write operations during security incidents
- **Scheduled Maintenance**: Allow read access while performing updates
- **Demo Environments**: Prevent data modification in demo/staging environments
- **Access Control**: Combine with IP whitelisting for secure maintenance access

## Requirements

- PHP 8.2+
- Laravel 11.x or 12.x

## Documentation

- [Installation](installation.md)
- [Configuration](configuration.md)
- [Usage](usage.md)
- [Changelog](changelog.md)
