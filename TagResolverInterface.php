<?php

namespace Mezied\TelescopeSmartTags\TagResolvers;

use Laravel\Telescope\IncomingEntry;

interface TagResolverInterface
{
    /**
     * Determine if this resolver should handle the given entry.
     */
    public function supports(IncomingEntry $entry): bool;

    /**
     * Resolve and return an array of tags for the given entry.
     *
     * @return string[]
     */
    public function resolve(IncomingEntry $entry): array;
}
