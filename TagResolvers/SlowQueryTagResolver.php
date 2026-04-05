<?php

declare(strict_types=1);

namespace Imezied\TelescopeSmartTags\TagResolvers;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

class SlowQueryTagResolver implements TagResolverInterface
{
    public function __construct(
        private readonly int $warnThresholdMs = 500,
        private readonly int $criticalThresholdMs = 2000,
    ) {}

    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::QUERY;
    }

    /** @return list<string> */
    public function resolve(IncomingEntry $entry): array
    {
        $duration = (int) ($entry->content['time'] ?? 0);

        return match (true) {
            $duration >= $this->criticalThresholdMs => ['slow-query', 'slow-query:critical'],
            $duration >= $this->warnThresholdMs     => ['slow-query', 'slow-query:warn'],
            default                                  => [],
        };
    }
}
