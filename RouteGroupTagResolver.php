<?php

namespace Mezied\TelescopeSmartTags\TagResolvers;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

class RouteGroupTagResolver implements TagResolverInterface
{
    public function __construct(
        /**
         * Map of URI prefix patterns => tag name.
         *
         * Example:
         * [
         *   'api/v1'   => 'group:api-v1',
         *   'api'      => 'group:api',
         *   'webhook'  => 'group:webhook',
         *   'admin'    => 'group:admin',
         * ]
         */
        protected array $prefixMap = [],

        /**
         * Map of route name prefixes => tag name.
         *
         * Example:
         * [
         *   'api.'     => 'group:api',
         *   'admin.'   => 'group:admin',
         * ]
         */
        protected array $routeNamePrefixMap = [],
    ) {}

    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::REQUEST;
    }

    public function resolve(IncomingEntry $entry): array
    {
        $tags = [];
        $uri  = ltrim($entry->content['uri'] ?? '', '/');
        $routeName = $entry->content['controller_action'] ?? '';

        // Match URI prefix patterns (longest match wins)
        $matchedPrefixes = [];
        foreach ($this->prefixMap as $prefix => $tag) {
            if (str_starts_with($uri, ltrim($prefix, '/'))) {
                $matchedPrefixes[strlen($prefix)] = $tag;
            }
        }

        if (! empty($matchedPrefixes)) {
            krsort($matchedPrefixes); // Longest prefix first
            $tags[] = reset($matchedPrefixes);
        }

        // Match route name prefixes
        foreach ($this->routeNamePrefixMap as $namePrefix => $tag) {
            if (str_starts_with($routeName, $namePrefix)) {
                $tags[] = $tag;
                break;
            }
        }

        return array_unique($tags);
    }
}
