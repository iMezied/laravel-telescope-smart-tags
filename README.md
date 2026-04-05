# 🔖 telescope-smart-tags

[![Latest Version on Packagist](https://img.shields.io/packagist/v/imezied/telescope-smart-tags.svg?style=flat-square)](https://packagist.org/packages/imezied/telescope-smart-tags)
[![Tests](https://img.shields.io/github/actions/workflow/status/imezied/telescope-smart-tags/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/imezied/telescope-smart-tags/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/imezied/telescope-smart-tags.svg?style=flat-square)](https://packagist.org/packages/imezied/telescope-smart-tags)
[![PHP Version](https://img.shields.io/packagist/php-v/imezied/telescope-smart-tags.svg?style=flat-square)](https://packagist.org/packages/imezied/telescope-smart-tags)
[![License](https://img.shields.io/packagist/l/imezied/telescope-smart-tags.svg?style=flat-square)](https://packagist.org/packages/imezied/telescope-smart-tags)

**Smart, zero-config auto-tagging for Laravel Telescope.**

Out of the box, Telescope's tag system is powerful but manual. This package automatically enriches every entry with meaningful, filterable tags — by HTTP status, exception type, response time, route group, and more — so you can instantly find what matters.

```
# Instead of scrolling through hundreds of requests...
# Just filter by tag:

validation-failed    → all 422 responses
error:5xx            → all server errors
slow:critical        → requests over 3s
carrier:dhl          → your custom domain tags
family:database      → all DB-related exceptions
```

---

## Features

- **HTTP Status Tags** — `http:422`, `error:4xx`, `validation-failed`, `rate-limited`, `server-error`, and more
- **Exception Tags** — `exception:QueryException`, `family:database`, `family:auth`, `family:validation`
- **Slow Request Tags** — `slow:warn` and `slow:critical` with configurable thresholds
- **Slow Query Tags** — `slow-query:warn` and `slow-query:critical` on DB watcher entries
- **Route Group Tags** — `group:api`, `group:webhook`, `group:admin` via URI prefix matching
- **Auth Context Tags** — `auth:authenticated` vs `auth:guest`
- **Custom Resolvers** — plug in your own domain logic (e.g. `carrier:dhl`, `tenant:acme`)
- **Fully configurable** — enable/disable each resolver independently, tune thresholds, extend semantic maps

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | `^8.3` |
| Laravel | `^11.0 \| ^12.0 \| ^13.0` |
| laravel/telescope | `^5.0` |

---

## Installation

```bash
composer require imezied/telescope-smart-tags --dev
```

That's it. The package auto-discovers and registers itself via Laravel's service provider. All resolvers are active by default with sensible defaults.

### Publish the config (optional)

```bash
php artisan vendor:publish --tag=telescope-smart-tags-config
```

This creates `config/telescope-smart-tags.php` where you can tune every resolver.

---

## Tags Reference

### HTTP Status Tags

Applied to every **Request** entry.

| Tag | When |
|---|---|
| `http:200`, `http:422`, `http:500` | Exact status code (always) |
| `success:2xx` | Status 200–299 |
| `redirect:3xx` | Status 300–399 |
| `error:4xx` | Status 400–499 |
| `error:5xx` | Status 500–599 |
| `bad-request` | 400 |
| `unauthorized` | 401 |
| `forbidden` | 403 |
| `not-found` | 404 |
| `csrf-mismatch` | 419 |
| `validation-failed` | 422 |
| `rate-limited` | 429 |
| `server-error` | 500 |
| `service-unavailable` | 503 |

### Exception Tags

Applied to every **Exception** entry.

| Tag | Example |
|---|---|
| `exception:{ClassName}` | `exception:QueryException` |
| `exception-fqn:{dotted.namespace}` | `exception-fqn:Illuminate.Database.QueryException` |
| `family:validation` | `ValidationException` |
| `family:auth` | `AuthenticationException`, `AuthorizationException` |
| `family:database` | `QueryException`, `ModelNotFoundException` |
| `family:rate-limit` | `ThrottleRequestsException` |
| `family:http` | `NotFoundHttpException`, `MethodNotAllowedHttpException` |
| `family:logic` | `BadMethodCallException`, `InvalidArgumentException` |
| `family:runtime` | `RuntimeException` |
| `family:type` | `TypeError` |

### Slow Request Tags

Applied to **Request** entries exceeding configured thresholds.

| Tag | Default Threshold |
|---|---|
| `slow` | > 1,000ms |
| `slow:warn` | > 1,000ms |
| `slow:critical` | > 3,000ms |

### Slow Query Tags

Applied to **Query** entries exceeding configured thresholds.

| Tag | Default Threshold |
|---|---|
| `slow-query` | > 500ms |
| `slow-query:warn` | > 500ms |
| `slow-query:critical` | > 2,000ms |

### Auth Context Tags

| Tag | When |
|---|---|
| `auth:authenticated` | Request has a logged-in user |
| `auth:guest` | No authenticated user |

---

## Configuration

```php
// config/telescope-smart-tags.php

return [

    'enabled' => env('TELESCOPE_SMART_TAGS_ENABLED', true),

    'resolvers' => [
        'http_status'   => true,
        'exceptions'    => true,
        'slow_requests' => true,
        'slow_queries'  => true,
        'route_groups'  => false, // opt-in
        'auth_context'  => false, // opt-in
    ],

    'http_status' => [
        'include_exact'    => true,
        'include_family'   => true,
        'include_semantic' => true,
        'custom_map' => [
            // 418 => 'im-a-teapot',
        ],
    ],

    'exceptions' => [
        'include_class'     => true,
        'include_family'    => true,
        'custom_family_map' => [
            // 'App\Exceptions\PaymentGatewayException' => 'family:payment',
        ],
    ],

    'slow_requests' => [
        'warn_ms'     => env('TELESCOPE_SLOW_REQUEST_WARN_MS', 1000),
        'critical_ms' => env('TELESCOPE_SLOW_REQUEST_CRITICAL_MS', 3000),
    ],

    'slow_queries' => [
        'warn_ms'     => env('TELESCOPE_SLOW_QUERY_WARN_MS', 500),
        'critical_ms' => env('TELESCOPE_SLOW_QUERY_CRITICAL_MS', 2000),
    ],

    'route_groups' => [
        'prefix_map' => [
            // 'api/v2'  => 'group:api-v2',
            // 'api'     => 'group:api',
            // 'webhook' => 'group:webhook',
            // 'admin'   => 'group:admin',
        ],
        'route_name_map' => [
            // 'api.'   => 'group:api',
            // 'admin.' => 'group:admin',
        ],
    ],

    'custom_resolvers' => [
        // App\Telescope\CarrierTagResolver::class,
        // App\Telescope\TenantTagResolver::class,
    ],
];
```

### Environment Variables

```dotenv
TELESCOPE_SMART_TAGS_ENABLED=true

TELESCOPE_SLOW_REQUEST_WARN_MS=1000
TELESCOPE_SLOW_REQUEST_CRITICAL_MS=3000

TELESCOPE_SLOW_QUERY_WARN_MS=500
TELESCOPE_SLOW_QUERY_CRITICAL_MS=2000
```

---

## Custom Resolvers

Build domain-specific resolvers by implementing `TagResolverInterface`:

```php
namespace App\Telescope;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use imezied\TelescopeSmartTags\TagResolvers\TagResolverInterface;

class CarrierTagResolver implements TagResolverInterface
{
    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::REQUEST;
    }

    public function resolve(IncomingEntry $entry): array
    {
        $carrier = $entry->content['payload']['carrier'] ?? null;

        return $carrier ? ["carrier:{$carrier}"] : [];
    }
}
```

Register in your config:

```php
'custom_resolvers' => [
    App\Telescope\CarrierTagResolver::class,
],
```

Your resolver is resolved through the Laravel service container, so constructor injection works:

```php
class TenantTagResolver implements TagResolverInterface
{
    public function __construct(private TenantRepository $tenants) {}

    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::REQUEST;
    }

    public function resolve(IncomingEntry $entry): array
    {
        $host   = $entry->content['headers']['host'][0] ?? null;
        $tenant = $host ? $this->tenants->findByDomain($host) : null;

        return $tenant ? ["tenant:{$tenant->slug}"] : [];
    }
}
```

### Manipulating the Registry at Runtime

You can also interact with the `TagRegistry` directly in a service provider:

```php
use imezied\TelescopeSmartTags\TagRegistry;

public function boot(): void
{
    $this->app->make(TagRegistry::class)
        ->add(new MyCustomResolver())
        ->add(new AnotherResolver());
}
```

---

## How It Works

The package hooks into Telescope's `Telescope::tag()` callback during the `boot` phase:

```
Request/Exception/Query
        │
        ▼
  TagRegistry::resolve()
        │
        ├── HttpStatusTagResolver::resolve()   → ['http:422', 'error:4xx', 'validation-failed']
        ├── SlowRequestTagResolver::resolve()  → ['slow', 'slow:warn']
        ├── RouteGroupTagResolver::resolve()   → ['group:api']
        └── [your custom resolvers]            → ['carrier:dhl', 'tenant:acme']
        │
        ▼
  Merged + Deduplicated Tags
        │
        ▼
  Telescope Entry (searchable in dashboard)
```

Each resolver is independently toggled, zero overhead if disabled.

---

## Testing Your Custom Resolver

```php
use imezied\TelescopeSmartTags\Tests\TestCase;
use App\Telescope\CarrierTagResolver;

class CarrierTagResolverTest extends TestCase
{
    public function test_tags_carrier_from_payload(): void
    {
        $resolver = new CarrierTagResolver();

        $entry = $this->makeRequestEntry([
            'payload' => ['carrier' => 'dhl'],
        ]);

        $tags = $resolver->resolve($entry);

        $this->assertContains('carrier:dhl', $tags);
    }

    public function test_returns_empty_without_carrier(): void
    {
        $resolver = new CarrierTagResolver();
        $entry    = $this->makeRequestEntry(['payload' => []]);

        $this->assertEmpty($resolver->resolve($entry));
    }
}
```

---

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

---

## Contributing

Pull requests are welcome. For major changes, please open an issue first.

1. Fork the repo
2. Create your branch (`git checkout -b feature/my-resolver`)
3. Add tests for your resolver
4. Run `composer test` — all tests must pass
5. Run `composer lint` — code style must pass
6. Submit your PR

---

## License

MIT — see [LICENSE](LICENSE) for details.
