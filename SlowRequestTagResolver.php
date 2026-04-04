<?php

declare(strict_types=1);

namespace Mezied\TelescopeSmartTags\TagResolvers;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

class SlowRequestTagResolver implements TagResolverInterface
{
    public function __construct(
        private readonly int $warnThresholdMs = 1000,
        private readonly int $criticalThresholdMs = 3000,
    ) {}

    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::REQUEST;
    }

    /** @return list<string> */
    public function resolve(IncomingEntry $entry): array
    {
        $duration = (int) ($entry->content['duration'] ?? 0);

        return match (true) {
            $duration >= $this->criticalThresholdMs => ['slow', 'slow:critical'],
            $duration >= $this->warnThresholdMs     => ['slow', 'slow:warn'],
            default                                  => [],
        };
    }
}
