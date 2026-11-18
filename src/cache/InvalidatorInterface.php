<?php

declare(strict_types=1);

namespace parallelogram\craftkit\cache;

/**
 * Invalidates everything cached under any of the given tags, and lets other
 * plugins (such as a full-page cache) know which tags changed.
 *
 * @api
 */
interface InvalidatorInterface
{
    /**
     * @param list<string> $tags
     */
    public function invalidate(array $tags): void;
}
