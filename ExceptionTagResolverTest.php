<?php

namespace Imezied\TelescopeSmartTags\Tests\Unit\TagResolvers;

use Imezied\TelescopeSmartTags\TagResolvers\ExceptionTagResolver;
use Imezied\TelescopeSmartTags\Tests\TestCase;

class ExceptionTagResolverTest extends TestCase
{
    private ExceptionTagResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ExceptionTagResolver();
    }

    public function test_supports_exception_entries(): void
    {
        $entry = $this->makeExceptionEntry();
        $this->assertTrue($this->resolver->supports($entry));
    }

    public function test_does_not_support_request_entries(): void
    {
        $entry = $this->makeRequestEntry();
        $this->assertFalse($this->resolver->supports($entry));
    }

    public function test_tags_exception_class_name(): void
    {
        $entry = $this->makeExceptionEntry([
            'class' => 'Illuminate\Validation\ValidationException',
        ]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('exception:ValidationException', $tags);
    }

    public function test_tags_exception_family_for_validation(): void
    {
        $entry = $this->makeExceptionEntry([
            'class' => 'Illuminate\Validation\ValidationException',
        ]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('family:validation', $tags);
    }

    public function test_tags_exception_family_for_database(): void
    {
        $entry = $this->makeExceptionEntry([
            'class' => 'Illuminate\Database\QueryException',
        ]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('family:database', $tags);
    }

    public function test_tags_exception_family_for_auth(): void
    {
        $entry = $this->makeExceptionEntry([
            'class' => 'Illuminate\Auth\AuthenticationException',
        ]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('family:auth', $tags);
    }

    public function test_returns_empty_for_missing_class(): void
    {
        $entry = $this->makeExceptionEntry(['class' => null]);
        $tags = $this->resolver->resolve($entry);

        $this->assertEmpty($tags);
    }

    public function test_custom_family_map_is_respected(): void
    {
        $resolver = new ExceptionTagResolver(
            exceptionFamilyMap: ['App\Exceptions\PaymentException' => 'family:payment']
        );
        $entry = $this->makeExceptionEntry(['class' => 'App\Exceptions\PaymentException']);
        $tags  = $resolver->resolve($entry);

        $this->assertContains('family:payment', $tags);
    }

    public function test_class_tags_can_be_disabled(): void
    {
        $resolver = new ExceptionTagResolver(includeExceptionClass: false);
        $entry    = $this->makeExceptionEntry(['class' => 'RuntimeException']);
        $tags     = $resolver->resolve($entry);

        $this->assertNotContains('exception:RuntimeException', $tags);
    }
}
