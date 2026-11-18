<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft;

use Override;
use parallelogram\craftkit\cache\TaggedStoreInterface;
use parallelogram\craftkit\cache\TaggedValue;
use parallelogram\craftkit\cache\TagList;
use yii\caching\CacheInterface;
use yii\caching\TagDependency;

use function array_key_exists;
use function is_array;

/**
 * Stores tagged values in a Yii cache with a TagDependency. Give it Craft's
 * main cache component, so Craft's element saves invalidate the same tags.
 *
 * @api
 */
final readonly class YiiTaggedStore implements TaggedStoreInterface
{
    public function __construct(
        private CacheInterface $cache,
        private string $keyPrefix = 'craftkit:',
    ) {}

    private static function unpack(mixed $data): ?TaggedValue
    {
        if (! is_array($data) || ! array_key_exists('value', $data) || ! is_array($data['tags'] ?? null)) {
            return null;
        }

        return new TaggedValue($data['value'], TagList::from($data['tags']));
    }

    #[Override]
    public function get(string $key): ?TaggedValue
    {
        return self::unpack($this->cache->get($this->keyPrefix . $key));
    }

    #[Override]
    public function set(string $key, mixed $value, array $tags, ?int $duration = null): void
    {
        $this->cache->set(
            $this->keyPrefix . $key,
            ['value' => $value, 'tags' => $tags],
            $duration ?? 0,
            [] === $tags ? null : new TagDependency(['tags' => $tags]),
        );
    }
}
