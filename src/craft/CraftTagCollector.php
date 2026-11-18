<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft;

use craft\services\Elements;
use Override;
use parallelogram\craftkit\cache\CollectedTags;
use parallelogram\craftkit\cache\TagCollectorInterface;
use parallelogram\craftkit\cache\TagList;

/**
 * Collects tags through Craft's own element cache-info collection, the same
 * mechanism `{% cache %}` uses, so element queries and loaded elements tag
 * the output automatically.
 *
 * @api
 */
final readonly class CraftTagCollector implements TagCollectorInterface
{
    public function __construct(
        private Elements $elements,
    ) {}

    #[Override]
    public function collect(array $tags): void
    {
        $this->elements->collectCacheTags($tags);
    }

    #[Override]
    public function isCollecting(): bool
    {
        return $this->elements->getIsCollectingCacheInfo();
    }

    #[Override]
    public function start(): void
    {
        $this->elements->startCollectingCacheInfo();
    }

    #[Override]
    public function stop(): CollectedTags
    {
        [$dependency, $duration] = $this->elements->stopCollectingCacheInfo();

        return new CollectedTags(TagList::from($dependency?->tags), $duration);
    }
}
