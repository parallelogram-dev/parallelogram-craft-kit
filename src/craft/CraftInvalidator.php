<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft;

use Override;
use parallelogram\craftkit\cache\InvalidatorInterface;
use yii\base\Event;
use yii\caching\CacheInterface;
use yii\caching\TagDependency;

/**
 * Invalidates tags in a Yii cache and announces them with
 * {@see TagsInvalidatedEvent}, so a full-page cache can purge the pages that
 * carry them.
 *
 * @api
 */
final readonly class CraftInvalidator implements InvalidatorInterface
{
    public const string EVENT_INVALIDATE = 'craftkitInvalidate';

    public function __construct(
        private CacheInterface $cache,
    ) {}

    #[Override]
    public function invalidate(array $tags): void
    {
        if ([] === $tags) {
            return;
        }

        TagDependency::invalidate($this->cache, $tags);

        Event::trigger(self::class, self::EVENT_INVALIDATE, new TagsInvalidatedEvent(['tags' => $tags]));
    }
}
