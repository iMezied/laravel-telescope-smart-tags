# Changelog

All notable changes to `telescope-smart-tags` will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

---

## [2.0.0] - 2026-04-04

### Added
- Laravel 13 support (released March 17, 2026)
- PHP 8.5 compatibility verified and added to CI matrix
- `declare(strict_types=1)` across all source files
- Typed class constants (`const array`) on `HttpStatusTagResolver` and `ExceptionTagResolver` using PHP 8.3 syntax
- `readonly` constructor properties across all resolvers for immutability guarantees
- `list<string>` return type annotations on all `resolve()` methods
- `static fn()` short closures in `ServiceProvider` where appropriate
- Stricter null/type checks in resolvers (`is_int`, `is_string`, `is_numeric`) replacing loose truthiness checks

### Changed
- **BREAKING**: Minimum PHP version raised from `^8.1` to `^8.3`
- **BREAKING**: Minimum Laravel version raised from `^10.0` to `^11.0`
- **BREAKING**: `laravel/telescope` requirement tightened to `^5.0` (drops Telescope 4.x)
- `orchestra/testbench` dev requirement updated to `^9.0|^10.0|^11.0`
- `phpunit/phpunit` dev requirement updated to `^11.0|^12.0`
- CI matrix now targets PHP `8.3`, `8.4`, `8.5` × Laravel `11.*`, `12.*`, `13.*`
- `buildRegistry()` visibility changed from `protected` to `private` (not intended for extension)
- `Telescope::tag()` closure changed to `static` to avoid implicit `$this` capture

### Removed
- PHP 8.1 and 8.2 support
- Laravel 10 support
- Telescope 4.x support

---

## [1.0.0] - 2026-04-02

### Added
- `HttpStatusTagResolver` — tags requests with exact status (`http:422`), family (`error:4xx`), and semantic aliases (`validation-failed`, `rate-limited`, `server-error`, etc.)
- `ExceptionTagResolver` — tags exceptions with class name (`exception:QueryException`), FQN, and family (`family:database`, `family:auth`, `family:validation`, etc.)
- `SlowRequestTagResolver` — tags slow requests with `slow:warn` and `slow:critical` based on configurable ms thresholds
- `SlowQueryTagResolver` — tags slow DB queries with `slow-query:warn` and `slow-query:critical`
- `RouteGroupTagResolver` — tags requests by URI prefix or route name prefix (`group:api`, `group:webhook`, `group:admin`)
- `AuthContextTagResolver` — tags requests as `auth:authenticated` or `auth:guest`
- `TagRegistry` — central resolver chain with `add()`, `prepend()`, `flush()`, and `resolve()` methods
- `TagResolverInterface` — contract for building custom domain resolvers
- Full config file (`config/telescope-smart-tags.php`) with per-resolver toggles and threshold tuning
- Laravel service container support for custom resolver injection
- Support for Laravel 10, 11, 12 and PHP 8.1, 8.2, 8.3
- Full PHPUnit test suite across all resolvers and the registry
- GitHub Actions CI matrix across PHP × Laravel versions
