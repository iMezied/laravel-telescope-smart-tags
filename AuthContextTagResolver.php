<?php

declare(strict_types=1);

namespace Mezied\TelescopeSmartTags\TagResolvers;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

class AuthContextTagResolver implements TagResolverInterface
{
    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::REQUEST;
    }

    /** @return list<string> */
    public function resolve(IncomingEntry $entry): array
    {
        $userId = $entry->content['user']['id'] ?? null;

        return $userId !== null
            ? ['auth:authenticated']
            : ['auth:guest'];
    }
}
