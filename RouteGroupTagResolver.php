<?php

declare(strict_types=1);

namespace Mezied\TelescopeSmartTags\TagResolvers;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

class RouteGroupTagResolver implements TagResolverInterface
{
    /**
     * @param  array<string, string>  $prefixMap       URI prefix  => tag name (longest match wins)
     * @param  array<string, string>  $routeNamePrefixMap  Route name prefix => tag name
     */
    public function __construct(
        private readonly array $prefixMap = [],
        private readonly array $routeNamePrefixMap = [],
    ) {}

    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::REQUEST;
    }

    /** @return list<string> */
    public function resolve(IncomingEntry $entry): array
    {
        $tags      = [];
        $uri       = ltrim((string) ($entry->content['uri'] ?? ''), '/');
        $routeName = (string) ($entry->content['controller_action'] ?? '');

        // URI prefix match — longest prefix wins
        $matchedPrefixes = [];

        foreach ($this->prefixMap as $prefix => $tag) {
            if (str_starts_with($uri, ltrim($prefix, '/'))) {
                $matchedPrefixes[strlen($prefix)] = $tag;
            }
        }

        if ($matchedPrefixes !== []) {
            krsort($matchedPrefixes);
            $tags[] = reset($matchedPrefixes);
        }

        // Route name prefix match
        foreach ($this->routeNamePrefixMap as $namePrefix => $tag) {
            if (str_starts_with($routeName, $namePrefix)) {
                $tags[] = $tag;
                break;
            }
        }

        return array_values(array_unique($tags));
    }
}
