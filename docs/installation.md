---
title: Installation
weight: 5
---

You can install the package via composer:

```bash
composer require rappasoft/lockout
```

## Version Compatibility

| Laravel | Lockout |
|:--------|:--------|
| 6.x     | 1.x     |
| 7.x     | 2.x     |
| 8.x     | 3.x     |
| 9.x     | 4.x     |
| 10.x    | 5.x     |
| 11.x    | 6.x     |
| 12.x    | 6.x     |
| 13.x    | 6.x     |

Lockout 6 requires PHP 8.2 or higher. Laravel 13 requires PHP 8.3 or higher.

Laravel 11 remains compatible with Lockout, but has reached the end of its security support. For production applications, use a patched Laravel 12 or 13 release. Laravel 11 compatibility tests allow only its known unpatched framework advisories: `PKSA-d5tc-s1qs-h781`, `PKSA-m5cs-t1y6-qpcs`, `PKSA-3r5d-mb8f-1qw9`, and `PKSA-mdq4-51ck-6kdq`. These exceptions are confined to CI and are not part of the package configuration.

