<?php

namespace Imezied\TelescopeSmartTags\Tests\Unit\TagResolvers;

use Imezied\TelescopeSmartTags\TagResolvers\RouteGroupTagResolver;
use Imezied\TelescopeSmartTags\Tests\TestCase;

class RouteGroupTagResolverTest extends TestCase
{
    public function test_matches_uri_prefix(): void
    {
        $resolver = new RouteGroupTagResolver(prefixMap: [
            'api' => 'group:api',
        ]);

        $entry = $this->makeRequestEntry(['uri' => '/api/users']);
        $tags  = $resolver->resolve($entry);

        $this->assertContains('group:api', $tags);
    }

    public function test_longest_prefix_wins(): void
    {
        $resolver = new RouteGroupTagResolver(prefixMap: [
            'api'    => 'group:api',
            'api/v2' => 'group:api-v2',
        ]);

        $entry = $this->makeRequestEntry(['uri' => '/api/v2/shipments']);
        $tags  = $resolver->resolve($entry);

        $this->assertContains('group:api-v2', $tags);
        $this->assertNotContains('group:api', $tags);
    }

    public function test_no_match_returns_empty(): void
    {
        $resolver = new RouteGroupTagResolver(prefixMap: [
            'webhook' => 'group:webhook',
        ]);

        $entry = $this->makeRequestEntry(['uri' => '/api/users']);
        $tags  = $resolver->resolve($entry);

        $this->assertEmpty($tags);
    }

    public function test_leading_slash_in_uri_is_handled(): void
    {
        $resolver = new RouteGroupTagResolver(prefixMap: [
            'admin' => 'group:admin',
        ]);

        $entry = $this->makeRequestEntry(['uri' => '/admin/dashboard']);
        $tags  = $resolver->resolve($entry);

        $this->assertContains('group:admin', $tags);
    }
}
