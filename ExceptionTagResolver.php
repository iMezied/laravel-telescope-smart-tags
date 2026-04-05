<?php

declare(strict_types=1);

namespace Imezied\TelescopeSmartTags\TagResolvers;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

class ExceptionTagResolver implements TagResolverInterface
{
    /** @var array<class-string, string> */
    private const array DEFAULT_FAMILY_MAP = [
        'Illuminate\Validation\ValidationException'              => 'family:validation',
        'Illuminate\Auth\AuthenticationException'                => 'family:auth',
        'Illuminate\Auth\Access\AuthorizationException'          => 'family:auth',
        'Illuminate\Database\Eloquent\ModelNotFoundException'    => 'family:database',
        'Illuminate\Database\QueryException'                     => 'family:database',
        'Illuminate\Http\Exceptions\ThrottleRequestsException'  => 'family:rate-limit',
        'Symfony\Component\HttpKernel\Exception\NotFoundHttpException'          => 'family:http',
        'Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException'  => 'family:http',
        'BadMethodCallException'  => 'family:logic',
        'InvalidArgumentException'=> 'family:logic',
        'RuntimeException'        => 'family:runtime',
        'TypeError'               => 'family:type',
    ];

    /** @var array<class-string, string> */
    private readonly array $exceptionFamilyMap;

    /**
     * @param  array<class-string, string>  $exceptionFamilyMap  Merged on top of the defaults
     */
    public function __construct(
        private readonly bool $includeExceptionClass = true,
        private readonly bool $includeExceptionFamily = true,
        array $exceptionFamilyMap = [],
    ) {
        $this->exceptionFamilyMap = [...self::DEFAULT_FAMILY_MAP, ...$exceptionFamilyMap];
    }

    public function supports(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::EXCEPTION;
    }

    /** @return list<string> */
    public function resolve(IncomingEntry $entry): array
    {
        $class = $entry->content['class'] ?? null;

        if (! is_string($class) || $class === '') {
            return [];
        }

        $tags = [];

        if ($this->includeExceptionClass) {
            $tags[] = 'exception:' . class_basename($class);
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
