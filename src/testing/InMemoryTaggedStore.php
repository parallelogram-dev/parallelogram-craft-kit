<?php

declare(strict_types=1);

namespace parallelogram\craftkit\testing;

use Override;
use parallelogram\craftkit\cache\InvalidatorInterface;
use parallelogram\craftkit\cache\TaggedStoreInterface;
use parallelogram\craftkit\cache\TaggedValue;

use function array_intersect;

/**
 * A tagged store for tests. It is also an invalidator, so a test can check
 * that invalidating a tag drops exactly the values stored under it.
 *
 * @api
 */
final class InMemoryTaggedStore implements TaggedStoreInterface, InvalidatorInterface
{
    /**
     * @var array<string, TaggedValue>
     */
    private array $entries = [];

    /**
     * @var array<string, int|null>
     */
    private array $durations = [];

    public function durationOf(string $key): ?int
    {
        return $this->durations[$key] ?? null;
    }

    #[Override]
    public function get(string $key): ?TaggedValue
    {
        return $this->entries[$key] ?? null;
    }

    #[Override]
    public function invalidate(array $tags): void
    {
        foreach ($this->entries as $key => $entry) {
            if ([] === array_intersect($entry->tags, $tags)) {
                continue;
            }

            unset($this->entries[$key], $this->durations[$key]);
        }
    }

    #[Override]
    public function set(string $key, mixed $value, array $tags, ?int $duration = null): void
    {
        $this->entries[$key]   = new TaggedValue($value, $tags);
        $this->durations[$key] = $duration;
    }
}
