<?php

declare(strict_types=1);

namespace parallelogram\craftkit\cache;

/**
 * Stores values alongside their tags, dropping them when any tag is
 * invalidated.
 *
 * @api
 */
interface TaggedStoreInterface
{
    public function get(string $key): ?TaggedValue;

    /**
     * @param list<string> $tags
     */
    public function set(string $key, mixed $value, array $tags, ?int $duration = null): void;
}
