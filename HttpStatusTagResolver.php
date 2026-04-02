<?php

namespace Mezied\TelescopeSmartTags\TagResolvers;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

class HttpStatusTagResolver implements TagResolverInterface
{
    /**
     * Semantic aliases for specific HTTP status codes.
     */
    protected array $semanticMap = [
        400 => 'bad-request',
        401 => 'unauthorized',
        403 => 'forbidden',
        404 => 'not-found',
        405 => 'method-not-allowed',
        408 => 'request-timeout',
        409 => 'conflict',
        410 => 'gone',
        415 => 'unsupported-media-type',
        419 => 'csrf-mismatch',
        422 => 'validation-failed',
        423 => 'locked',
        429 => 'rate-limited',
        500 => 'server-error',
        502 => 'bad-gateway',
        503 => 'service-unavailable',
        504 => 'gateway-timeout',
    ];

    public function __construct(
        protected bool $includeExactStatus = true,
        protected bool $includeStatusFamily = true,
        protected bool $includeSemanticAlias = true,
        protected array $customSemanticMap = [],
    ) {
        $this->semanticMap = array_merge($this->semanticMap, $customSemanticMap);
    }

    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::REQUEST;
    }

    public function resolve(IncomingEntry $entry): array
    {
        $status = $entry->content['response_status'] ?? null;

        if (! $status) {
            return [];
        }

        $tags = [];

        if ($this->includeExactStatus) {
            $tags[] = "http:{$status}";
        }

        if ($this->includeStatusFamily) {
            $tags[] = match (true) {
                $status >= 500 => 'error:5xx',
                $status >= 400 => 'error:4xx',
                $status >= 300 => 'redirect:3xx',
                $status >= 200 => 'success:2xx',
                default        => 'info:1xx',
            };
        }

        if ($this->includeSemanticAlias && isset($this->semanticMap[$status])) {
            $tags[] = $this->semanticMap[$status];
        }

        return $tags;
    }
}
