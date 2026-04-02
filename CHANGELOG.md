# Changelog

All notable changes to `telescope-smart-tags` will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

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
- Environment variable support for all thresholds
- Laravel service container support for custom resolver injection
- Support for Laravel 10, 11, 12 and PHP 8.1, 8.2, 8.3
- Full PHPUnit test suite across all resolvers and the registry
- GitHub Actions CI matrix across PHP × Laravel versions
