<?php

namespace Mezied\TelescopeSmartTags\TagResolvers;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

class ExceptionTagResolver implements TagResolverInterface
{
    public function __construct(
        protected bool $includeExceptionClass = true,
        protected bool $includeExceptionFamily = true,
        protected array $exceptionFamilyMap = [],
    ) {
        // Default family groupings for common Laravel/PHP exceptions
        $this->exceptionFamilyMap = array_merge([
            'Illuminate\Validation\ValidationException'          => 'family:validation',
            'Illuminate\Auth\AuthenticationException'            => 'family:auth',
            'Illuminate\Auth\Access\AuthorizationException'      => 'family:auth',
            'Illuminate\Database\Eloquent\ModelNotFoundException' => 'family:database',
            'Illuminate\Database\QueryException'                 => 'family:database',
            'Illuminate\Http\Exceptions\ThrottleRequestsException' => 'family:rate-limit',
            'Symfony\Component\HttpKernel\Exception\NotFoundHttpException' => 'family:http',
            'Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException' => 'family:http',
            'BadMethodCallException'                             => 'family:logic',
            'InvalidArgumentException'                           => 'family:logic',
            'RuntimeException'                                   => 'family:runtime',
            'TypeError'                                          => 'family:type',
        ], $exceptionFamilyMap);
    }

    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::EXCEPTION;
    }

    public function resolve(IncomingEntry $entry): array
    {
        $class = $entry->content['class'] ?? null;

        if (! $class) {
            return [];
        }

        $tags = [];

        if ($this->includeExceptionClass) {
            // e.g. "exception:ValidationException"
            $tags[] = 'exception:' . class_basename($class);
            // Full namespaced tag for precise filtering
            $tags[] = 'exception-fqn:' . str_replace('\\', '.', $class);
        }

        if ($this->includeExceptionFamily) {
            foreach ($this->exceptionFamilyMap as $exceptionClass => $familyTag) {
                if ($class === $exceptionClass || is_a($class, $exceptionClass, true)) {
                    $tags[] = $familyTag;
                    break;
                }
            }
        }

        return $tags;
    }
}
