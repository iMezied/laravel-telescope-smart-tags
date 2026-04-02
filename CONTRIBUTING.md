# Contributing

Thank you for considering contributing to `telescope-smart-tags`!

## Local Setup

```bash
git clone https://github.com/mezied/telescope-smart-tags
cd telescope-smart-tags
composer install
```

## Running Tests

```bash
composer test
```

## Code Style

We use [Laravel Pint](https://laravel.com/docs/pint) with the `laravel` preset.

```bash
composer lint        # fix
composer lint:check  # check only (CI uses this)
```

## Building a Custom Resolver

Every resolver must implement `TagResolverInterface`:

```php
use Laravel\Telescope\IncomingEntry;
use Mezied\TelescopeSmartTags\TagResolvers\TagResolverInterface;

class MyResolver implements TagResolverInterface
{
    public function supports(IncomingEntry $entry): bool
    {
        // Return true for the entry types this resolver handles
        return $entry->type === 'request';
    }

    public function resolve(IncomingEntry $entry): array
    {
        // Return an array of tag strings
        return ['my-tag'];
    }
}
```

**Rules for resolvers:**
- Must be deterministic — same entry always produces same tags
- Must not throw exceptions — return `[]` on error
- Must not perform I/O (HTTP calls, DB queries) — tags are resolved synchronously on every Telescope write
- Should be lightweight — they run on every recorded entry

## Pull Request Checklist

- [ ] New resolver has a corresponding test class in `tests/Unit/TagResolvers/`
- [ ] All tests pass (`composer test`)
- [ ] Code style passes (`composer lint:check`)
- [ ] `CHANGELOG.md` updated under `[Unreleased]`
- [ ] `README.md` updated if new resolver is included in the package
