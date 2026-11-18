<?php

declare(strict_types=1);

namespace parallelogram\craftkit\cache;

/**
 * Gathers the cache tags touched while a piece of output is produced.
 *
 * Collections nest: stopping an inner collection hands its tags to the
 * enclosing one, so a page that renders a cached menu ends up tagged with
 * everything the menu used.
 *
 * @api
 */
interface TagCollectorInterface
{
    /**
     * Adds tags to the open collection. Does nothing when none is open.
     *
     * @param list<string> $tags
     */
    public function collect(array $tags): void;

    public function isCollecting(): bool;

    /**
     * Opens a new collection, nested inside any that is already open.
     */
    public function start(): void;

    /**
     * Closes the innermost collection, merges its tags into the enclosing
     * collection (if any) and returns them.
     */
    public function stop(): CollectedTags;
}
