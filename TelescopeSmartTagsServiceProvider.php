<?php

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

        $this->app->singleton(TagRegistry::class, function () {
            return $this->buildRegistry();
        });

        // Alias for convenience
        $this->app->alias(TagRegistry::class, 'telescope-smart-tags');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/telescope-smart-tags.php' => config_path('telescope-smart-tags.php'),
        ], 'telescope-smart-tags-config');

        // Bail out if Telescope isn't installed or the package is disabled
        if (! class_exists(Telescope::class) || ! config('telescope-smart-tags.enabled', true)) {
            return;
        }

        $registry = $this->app->make(TagRegistry::class);

        Telescope::tag(function (IncomingEntry $entry) use ($registry): array {
            return $registry->resolve($entry);
        });
    }

    protected function buildRegistry(): TagRegistry
    {
        $config   = config('telescope-smart-tags');
        $registry = new TagRegistry();

        // --- HTTP Status ---
        if ($config['resolvers']['http_status'] ?? true) {
            $registry->add(new HttpStatusTagResolver(
                includeExactStatus:    $config['http_status']['include_exact']   ?? true,
                includeStatusFamily:   $config['http_status']['include_family']  ?? true,
                includeSemanticAlias:  $config['http_status']['include_semantic'] ?? true,
                customSemanticMap:     $config['http_status']['custom_map']       ?? [],
            ));
        }

        // --- Exceptions ---
        if ($config['resolvers']['exceptions'] ?? true) {
            $registry->add(new ExceptionTagResolver(
                includeExceptionClass:  $config['exceptions']['include_class']  ?? true,
                includeExceptionFamily: $config['exceptions']['include_family'] ?? true,
                exceptionFamilyMap:     $config['exceptions']['custom_family_map'] ?? [],
            ));
        }

        // --- Slow Requests ---
        if ($config['resolvers']['slow_requests'] ?? true) {
            $registry->add(new SlowRequestTagResolver(
                warnThresholdMs:     $config['slow_requests']['warn_ms']     ?? 1000,
                criticalThresholdMs: $config['slow_requests']['critical_ms'] ?? 3000,
            ));
        }

        // --- Slow Queries ---
        if ($config['resolvers']['slow_queries'] ?? true) {
            $registry->add(new SlowQueryTagResolver(
                warnThresholdMs:     $config['slow_queries']['warn_ms']     ?? 500,
                criticalThresholdMs: $config['slow_queries']['critical_ms'] ?? 2000,
            ));
        }

        // --- Route Groups ---
        if ($config['resolvers']['route_groups'] ?? false) {
            $registry->add(new RouteGroupTagResolver(
                prefixMap:          $config['route_groups']['prefix_map']       ?? [],
                routeNamePrefixMap: $config['route_groups']['route_name_map']   ?? [],
            ));
        }

        // --- Auth Context ---
        if ($config['resolvers']['auth_context'] ?? false) {
            $registry->add(new AuthContextTagResolver());
        }

        // --- Custom Resolvers ---
        foreach ($config['custom_resolvers'] ?? [] as $resolverClass) {
            $resolver = $this->app->make($resolverClass);

            if ($resolver instanceof TagResolverInterface) {
                $registry->add($resolver);
            }
        }

        return $registry;
    }
}
