# Changelog

All notable changes to `lockout` will be documented in this file

## [Unreleased]

### Added
- Laravel 13 compatibility while retaining Laravel 11 and 12 support.
- IPv6 CIDR matching using Symfony's existing IP utilities.

### Fixed
- Load and publish the bundled maintenance view from the package resources directory.
- Match route whitelist patterns literally except for `*` wildcards.
- Resolve named routes and API middleware before global middleware dispatch.
- Reject malformed CIDR masks instead of crashing or widening whitelist access.
- Make enable/disable commands update the active environment file atomically, preserve file permissions, fail on persistence errors, and clear stale configuration and status caches.

### Changed
- Use patched PHPUnit 11–13 releases and PHPUnit test attributes.
- Test every supported Laravel version with PHP 8.2–8.5 in CI, and audit dependencies.
- Retain Laravel 11 compatibility tests with a CI-only exception for its unpatched framework advisory; recommend patched Laravel 12 or 13 for production.

## [6.0.0] - 2025-01-XX

### Added
- Laravel 11 and Laravel 12 Support
- PHP 8.2+ Support

### Fixed
- Fixed middleware logic bug where allow_login check was inside locked_types loop
- Fixed method case sensitivity issues in whitelist and locked_types handling
- Fixed pages array validation to handle non-array values gracefully
- Improved type hints and code quality

### Changed
- Updated PHPUnit to v11
- Updated PHPUnit configuration for PHPUnit 11 compatibility
- Improved test coverage with 11 additional test cases

## [5.0.0] - 2023-04-11

### Added

- Laravel 10 Support

## [4.0.0] - 2022-02-21

### Added

- Laravel 9 Support

## [3.0.1] - 2020-12-13

### Changed

- PHP8 Support

## [3.0.0] - 2020-09-13

### Added

- Laravel 8 Support

## [2.0.0] - 2020-03-17

### Added

- Laravel 7 Support

## [1.0.1] - 2020-06-26

### Added

- Ability to define a list of pages to whitelist and by what request method.

## 1.0.0 - 2020-02-19

- Initial release

[Unreleased]: https://github.com/rappasoft/laravel-boilerplate/compare/v5.0.0...develop
[5.0.0]: https://github.com/rappasoft/laravel-boilerplate/compare/v4.0.0...v5.0.0
[4.0.0]: https://github.com/rappasoft/laravel-boilerplate/compare/v3.0.1...v4.0.0
[3.0.1]: https://github.com/rappasoft/laravel-boilerplate/compare/v3.0.0...v3.0.1
[3.0.0]: https://github.com/rappasoft/laravel-boilerplate/compare/v2.0.0...v3.0.0
[2.0.0]: https://github.com/rappasoft/laravel-boilerplate/compare/v1.0.1...v2.0.0
[1.0.1]: https://github.com/rappasoft/laravel-boilerplate/compare/v1.0.0...v1.0.1
