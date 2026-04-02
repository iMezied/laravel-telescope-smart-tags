<?php

/**
 * EXAMPLE: Custom Resolver
 *
 * This file is not part of the package — it demonstrates how to build your own
 * resolver and register it via config('telescope-smart-tags.custom_resolvers').
 *
 * Copy this to your app, e.g. app/Telescope/CarrierTagResolver.php
 */

namespace App\Telescope;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Mezied\TelescopeSmartTags\TagResolvers\TagResolverInterface;

/**
 * Tags requests by carrier name for multi-carrier logistics platforms.
 *
 * Reads the 'carrier' field from the request payload or URI and attaches
 * tags like "carrier:dhl", "carrier:fedex", "carrier:aramex".
 *
 * Register in config/telescope-smart-tags.php:
 *   'custom_resolvers' => [App\Telescope\CarrierTagResolver::class]
 */
class CarrierTagResolver implements TagResolverInterface
{
    protected array $knownCarriers = [
        'dhl', 'fedex', 'ups', 'aramex', 'spl', 'smsa',
    ];

    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::REQUEST;
    }

    public function resolve(IncomingEntry $entry): array
    {
        $tags    = [];
        $payload = $entry->content['payload'] ?? [];

        // Check JSON body
        $carrier = $payload['carrier'] ?? null;

        // Fallback: check URI for carrier slug
        if (! $carrier) {
            $uri = $entry->content['uri'] ?? '';
            foreach ($this->knownCarriers as $known) {
                if (str_contains(strtolower($uri), $known)) {
                    $carrier = $known;
                    break;
                }
            }
        }

        if ($carrier) {
            $tags[] = 'carrier:' . strtolower($carrier);
        }

        return $tags;
    }
}
