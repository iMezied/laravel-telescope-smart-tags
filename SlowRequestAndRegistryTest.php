<?php

namespace Mezied\TelescopeSmartTags\Tests\Unit\TagResolvers;

use Mezied\TelescopeSmartTags\TagRegistry;
use Mezied\TelescopeSmartTags\TagResolvers\HttpStatusTagResolver;
use Mezied\TelescopeSmartTags\TagResolvers\SlowRequestTagResolver;
use Mezied\TelescopeSmartTags\Tests\TestCase;

class SlowRequestTagResolverTest extends TestCase
{
    private SlowRequestTagResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new SlowRequestTagResolver(warnThresholdMs: 1000, criticalThresholdMs: 3000);
    }

    public function test_fast_requests_get_no_slow_tags(): void
    {
        $entry = $this->makeRequestEntry(['duration' => 200]);
        $tags = $this->resolver->resolve($entry);

        $this->assertNotContains('slow', $tags);
    }

    public function test_requests_over_warn_threshold_get_slow_warn(): void
    {
        $entry = $this->makeRequestEntry(['duration' => 1500]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('slow', $tags);
        $this->assertContains('slow:warn', $tags);
        $this->assertNotContains('slow:critical', $tags);
    }

    public function test_requests_over_critical_threshold_get_slow_critical(): void
    {
        $entry = $this->makeRequestEntry(['duration' => 5000]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('slow', $tags);
        $this->assertContains('slow:critical', $tags);
        $this->assertNotContains('slow:warn', $tags);
    }

    public function test_exact_warn_threshold_triggers_slow_warn(): void
    {
        $entry = $this->makeRequestEntry(['duration' => 1000]);
        $tags = $this->resolver->resolve($entry);

        $this->assertContains('slow:warn', $tags);
    }
}

class TagRegistryTest extends TestCase
{
    public function test_registry_merges_tags_from_multiple_resolvers(): void
    {
        $registry = new TagRegistry();
        $registry->add(new HttpStatusTagResolver());
        $registry->add(new SlowRequestTagResolver());

        $entry = $this->makeRequestEntry([
            'response_status' => 500,
            'duration'        => 5000,
        ]);

        $tags = $registry->resolve($entry);

        $this->assertContains('http:500', $tags);
        $this->assertContains('error:5xx', $tags);
        $this->assertContains('slow:critical', $tags);
    }

    public function test_registry_deduplicates_tags(): void
    {
        // Add same resolver twice
        $registry = new TagRegistry();
        $registry->add(new HttpStatusTagResolver());
        $registry->add(new HttpStatusTagResolver());

        $entry = $this->makeRequestEntry(['response_status' => 200]);
        $tags  = $registry->resolve($entry);

        $this->assertEquals(count($tags), count(array_unique($tags)));
    }

    public function test_registry_can_be_flushed(): void
    {
        $registry = new TagRegistry();
        $registry->add(new HttpStatusTagResolver());
        $registry->flush();

        $entry = $this->makeRequestEntry(['response_status' => 500]);
        $tags  = $registry->resolve($entry);

        $this->assertEmpty($tags);
    }

    public function test_resolvers_can_be_prepended(): void
    {
        $registry = new TagRegistry();
        $registry->add(new HttpStatusTagResolver());
        $registry->prepend(new SlowRequestTagResolver());

        $this->assertInstanceOf(SlowRequestTagResolver::class, $registry->all()[0]);
        $this->assertInstanceOf(HttpStatusTagResolver::class, $registry->all()[1]);
    }
}
