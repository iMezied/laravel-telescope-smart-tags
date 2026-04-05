<?php

declare(strict_types=1);

namespace Imezied\TelescopeSmartTags\TagResolvers;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

class HttpStatusTagResolver implements TagResolverInterface
{
    /** @var array<int, string> Default semantic aliases for well-known HTTP status codes */
    private const array DEFAULT_SEMANTIC_MAP = [
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

    /** @var array<int, string> */
    private readonly array $semanticMap;

    /**
     * @param  array<int, string>  $customSemanticMap  Override or extend the default semantic map
     */
    public function __construct(
        private readonly bool $includeExactStatus = true,
        private readonly bool $includeStatusFamily = true,
        private readonly bool $includeSemanticAlias = true,
        array $customSemanticMap = [],
    ) {
        $this->semanticMap = [...self::DEFAULT_SEMANTIC_MAP, ...$customSemanticMap];
    }

    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::REQUEST;
    }

    /** @return list<string> */
    public function resolve(IncomingEntry $entry): array
    {
        $status = $entry->content['response_status'] ?? null;

        if (! is_int($status) && ! is_numeric($status)) {
            return [];
        }

        $status = (int) $status;
        $tags   = [];

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

        if ($this->includeSemanticAlias && array_key_exists($status, $this->semanticMap)) {
            $tags[] = $this->semanticMap[$status];
        }

        return $tags;
    }
}
