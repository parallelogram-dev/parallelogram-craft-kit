<?php

declare(strict_types=1);

namespace parallelogram\craftkit\cache;

/**
 * A cached value together with the tags it was produced under.
 *
 * @api
 */
final readonly class TaggedValue
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        public mixed $value,
        public array $tags,
    ) {}
}
