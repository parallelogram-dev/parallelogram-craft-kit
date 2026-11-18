<?php

declare(strict_types=1);

namespace parallelogram\craftkit\cache;

/**
 * The tags gathered while a value was produced, and the longest the value
 * should be kept for (null means no limit beyond the store's default).
 *
 * @api
 */
final readonly class CollectedTags
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        public array $tags,
        public ?int $duration = null,
    ) {}
}
