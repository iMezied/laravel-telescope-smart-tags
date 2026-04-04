<?php

declare(strict_types=1);

namespace Mezied\TelescopeSmartTags;

use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Mezied\TelescopeSmartTags\TagResolvers\AuthContextTagResolver;
use Mezied\TelescopeSmartTags\TagResolvers\ExceptionTagResolver;
use Mezied\TelescopeSmartTags\TagResolvers\HttpStatusTagResolver;
use Mezied\TelescopeSmartTags\TagResolvers\RouteGroupTagResolver;
use Mezied\TelescopeSmartTags\TagResolvers\SlowQueryTagResolver;
use Mezied\TelescopeSmartTags\TagResolvers\SlowRequestTagResolver;
use Mezied\TelescopeSmartTags\TagResolvers\TagResolverInterface;

class TelescopeSmartTagsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/telescope-smart-tags.php',
            'telescope-smart-tags'
        );

        $this->app->singleton(TagRegistry::class, fn () => $this->buildRegistry());

        $this->app->alias(TagRegistry::class, 'telescope-smart-tags');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/telescope-smart-tags.php' => config_path('telescope-smart-tags.php'),
        ], 'telescope-smart-tags-config');

        if (! class_exists(Telescope::class) || ! config('telescope-smart-tags.enabled', true)) {
            return;
        }

        $registry = $this->app->make(TagRegistry::class);

        Telescope::tag(static function (IncomingEntry $entry) use ($registry): array {
            return $registry->resolve($entry);
        });
    }

    private function buildRegistry(): TagRegistry
    {
        /** @var array<string, mixed> $config */
        $config   = config('telescope-smart-tags', []);
        $registry = new TagRegistry();

        if ($config['resolvers']['http_status'] ?? true) {
            $registry->add(new HttpStatusTagResolver(
                includeExactStatus:   (bool) ($config['http_status']['include_exact']    ?? true),
                includeStatusFamily:  (bool) ($config['http_status']['include_family']   ?? true),
                includeSemanticAlias: (bool) ($config['http_status']['include_semantic'] ?? true),
                customSemanticMap:   (array) ($config['http_status']['custom_map']       ?? []),
            ));
        }

        if ($config['resolvers']['exceptions'] ?? true) {
            $registry->add(new ExceptionTagResolver(
                includeExceptionClass:  (bool)  ($config['exceptions']['include_class']      ?? true),
                includeExceptionFamily: (bool)  ($config['exceptions']['include_family']     ?? true),
                exceptionFamilyMap:     (array) ($config['exceptions']['custom_family_map']  ?? []),
            ));
        }

        if ($config['resolvers']['slow_requests'] ?? true) {
            $registry->add(new SlowRequestTagResolver(
                warnThresholdMs:     (int) ($config['slow_requests']['warn_ms']     ?? 1000),
                criticalThresholdMs: (int) ($config['slow_requests']['critical_ms'] ?? 3000),
            ));
        }

        if ($config['resolvers']['slow_queries'] ?? true) {
            $registry->add(new SlowQueryTagResolver(
                warnThresholdMs:     (int) ($config['slow_queries']['warn_ms']     ?? 500),
                criticalThresholdMs: (int) ($config['slow_queries']['critical_ms'] ?? 2000),
            ));
        }

        if ($config['resolvers']['route_groups'] ?? false) {
            $registry->add(new RouteGroupTagResolver(
                prefixMap:          (array) ($config['route_groups']['prefix_map']     ?? []),
                routeNamePrefixMap: (array) ($config['route_groups']['route_name_map'] ?? []),
            ));
        }

        if ($config['resolvers']['auth_context'] ?? false) {
            $registry->add(new AuthContextTagResolver());
        }

        foreach ((array) ($config['custom_resolvers'] ?? []) as $resolverClass) {
            $resolver = $this->app->make($resolverClass);

            if ($resolver instanceof TagResolverInterface) {
                $registry->add($resolver);
            }
        }

        return $registry;
    }
}
