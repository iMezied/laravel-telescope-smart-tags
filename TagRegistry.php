<?php

namespace Mezied\TelescopeSmartTags;

use Laravel\Telescope\IncomingEntry;
use Mezied\TelescopeSmartTags\TagResolvers\TagResolverInterface;

class TagRegistry
{
    /** @var TagResolverInterface[] */
    protected array $resolvers = [];

    public function add(TagResolverInterface $resolver): static
    {
        $this->resolvers[] = $resolver;

        return $this;
    }

    public function prepend(TagResolverInterface $resolver): static
    {
        array_unshift($this->resolvers, $resolver);

        return $this;
    }

    /**
     * Run all matching resolvers against the entry and return merged unique tags.
     *
     * @return string[]
     */
    public function resolve(IncomingEntry $entry): array
    {
        $tags = [];

        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($entry)) {
                foreach ($resolver->resolve($entry) as $tag) {
                    $tags[] = $tag;
                }
            }
        }

        return array_values(array_unique($tags));
    }

    /**
     * @return TagResolverInterface[]
     */
    public function all(): array
    {
        return $this->resolvers;
    }

    public function flush(): static
    {
        $this->resolvers = [];

        return $this;
    }
}
