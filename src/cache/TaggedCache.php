<?php

declare(strict_types=1);

namespace parallelogram\craftkit\cache;

/**
 * Caches a value and replays the tags it was produced under every time it is
 * served, so whatever encloses it (a page being cached in full, say) is
 * tagged as if the value had been produced afresh.
 *
 * @api
 */
final readonly class TaggedCache
{
    public function __construct(
        private TaggedStoreInterface $store,
        private TagCollectorInterface $collector,
    ) {}

    /**
     * Returns the cached value for the key, or produces, caches and returns it.
     *
     * @template T
     *
     * @param callable(): T $producer
     *
     * @return T
     */
    public function remember(string $key, callable $producer): mixed
    {
        $hit = $this->store->get($key);

        if (null !== $hit) {
            $this->collector->collect($hit->tags);

            /** @var T */
            return $hit->value;
        }

        $this->collector->start();

        try {
            $value = $producer();
        } finally {
            $collected = $this->collector->stop();
        }

        $this->store->set($key, $value, $collected->tags, $collected->duration);

        return $value;
    }
}
