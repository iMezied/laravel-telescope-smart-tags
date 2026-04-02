<?php

namespace Mezied\TelescopeSmartTags\Tests\Unit\TagResolvers;

use Mezied\TelescopeSmartTags\TagResolvers\HttpStatusTagResolver;
use Mezied\TelescopeSmartTags\Tests\TestCase;

class HttpStatusTagResolverTest extends TestCase
{
    private HttpStatusTagResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new HttpStatusTagResolver();
    }

    public function test_supports_request_entries(): void
    {
        $entry = $this->makeRequestEntry();
        $this->assertTrue($this->resolver->supports($entry));
    }

    public function test_does_not_support_exception_entries(): void
    {
        $entry = $this->makeExceptionEntry();
        $this->assertFalse($this->resolver->supports($entry));
    }

    public function test_tags_200_correctly(): void
    {
        $entry = $this->makeRequestEntry(['response_status' => 200]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('http:200', $tags);
        $this->assertContains('success:2xx', $tags);
    }

    public function test_tags_422_correctly(): void
    {
        $entry = $this->makeRequestEntry(['response_status' => 422]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('http:422', $tags);
        $this->assertContains('error:4xx', $tags);
        $this->assertContains('validation-failed', $tags);
    }

    public function test_tags_500_correctly(): void
    {
        $entry = $this->makeRequestEntry(['response_status' => 500]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('http:500', $tags);
        $this->assertContains('error:5xx', $tags);
        $this->assertContains('server-error', $tags);
    }

    public function test_tags_429_rate_limited(): void
    {
        $entry = $this->makeRequestEntry(['response_status' => 429]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('rate-limited', $tags);
    }

    public function test_tags_401_unauthorized(): void
    {
        $entry = $this->makeRequestEntry(['response_status' => 401]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('unauthorized', $tags);
        $this->assertContains('error:4xx', $tags);
    }

    public function test_tags_404_not_found(): void
    {
        $entry = $this->makeRequestEntry(['response_status' => 404]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('not-found', $tags);
    }

    public function test_tags_3xx_redirect(): void
    {
        $entry = $this->makeRequestEntry(['response_status' => 302]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('http:302', $tags);
        $this->assertContains('redirect:3xx', $tags);
    }

    public function test_returns_empty_for_missing_status(): void
    {
        $entry = $this->makeRequestEntry(['response_status' => null]);
        $tags = $this->resolver->resolve($entry);

        $this->assertEmpty($tags);
    }

    public function test_custom_semantic_map_is_merged(): void
    {
        $resolver = new HttpStatusTagResolver(customSemanticMap: [418 => 'im-a-teapot']);
        $entry    = $this->makeRequestEntry(['response_status' => 418]);
        $tags     = $resolver->resolve($entry);

        $this->assertContains('im-a-teapot', $tags);
    }

    public function test_exact_status_can_be_disabled(): void
    {
        $resolver = new HttpStatusTagResolver(includeExactStatus: false);
        $entry    = $this->makeRequestEntry(['response_status' => 500]);
        $tags     = $resolver->resolve($entry);

        $this->assertNotContains('http:500', $tags);
        $this->assertContains('error:5xx', $tags);
    }

    public function test_family_tags_can_be_disabled(): void
    {
        $resolver = new HttpStatusTagResolver(includeStatusFamily: false);
        $entry    = $this->makeRequestEntry(['response_status' => 500]);
        $tags     = $resolver->resolve($entry);

        $this->assertNotContains('error:5xx', $tags);
        $this->assertContains('http:500', $tags);
    }

    public function test_semantic_aliases_can_be_disabled(): void
    {
        $resolver = new HttpStatusTagResolver(includeSemanticAlias: false);
        $entry    = $this->makeRequestEntry(['response_status' => 422]);
        $tags     = $resolver->resolve($entry);

        $this->assertNotContains('validation-failed', $tags);
        $this->assertContains('http:422', $tags);
    }
}
