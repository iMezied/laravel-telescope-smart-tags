<?php

namespace Mezied\TelescopeSmartTags\TagResolvers;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

class SlowRequestTagResolver implements TagResolverInterface
{
    public function __construct(
        protected int $warnThresholdMs = 1000,
        protected int $criticalThresholdMs = 3000,
    ) {}

    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::REQUEST;
    }

    public function resolve(IncomingEntry $entry): array
    {
        $duration = $entry->content['duration'] ?? 0;

        return match (true) {
            $duration >= $this->criticalThresholdMs => ['slow', 'slow:critical'],
            $duration >= $this->warnThresholdMs     => ['slow', 'slow:warn'],
            default                                  => [],
        };
    }
}
