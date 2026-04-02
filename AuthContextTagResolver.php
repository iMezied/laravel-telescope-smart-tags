<?php

namespace Mezied\TelescopeSmartTags\TagResolvers;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

class AuthContextTagResolver implements TagResolverInterface
{
    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::REQUEST;
    }

    public function resolve(IncomingEntry $entry): array
    {
        $tags = [];

        $userId = $entry->content['user']['id'] ?? null;

        if ($userId) {
            $tags[] = 'auth:authenticated';
        } else {
            $tags[] = 'auth:guest';
        }

        return $tags;
    }
}
