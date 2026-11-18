<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft;

use parallelogram\craftkit\cache\TaggedValue;
use parallelogram\craftkit\cache\TagList;
use parallelogram\craftkit\craft\YiiTaggedStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use yii\caching\ArrayCache;
use yii\caching\CacheInterface;
use yii\caching\TagDependency;

/**
 * @internal
 */
#[CoversClass(YiiTaggedStore::class)]
#[UsesClass(TaggedValue::class)]
#[UsesClass(TagList::class)]
#[Group('unit')]
final class YiiTaggedStoreTest extends TestCase
{
    /**
     * @return array<string, array{mixed}>
     */
    public static function malformedEntryProvider(): array
    {
        return [
            'not an array'   => ['plain string'],
            'missing value'  => [['tags' => ['a']]],
            'missing tags'   => [['value' => 'x']],
            'tags not array' => [['value' => 'x', 'tags' => 'a']],
        ];
    }

    #[Test]
    public function dropsAValueWhenOneOfItsTagsIsInvalidated(): void
    {
        $cache = new ArrayCache();
        $store = new YiiTaggedStore($cache);
        $store->set('menu', value: 'html', tags: ['p::nav:main', 'element::7']);

        TagDependency::invalidate($cache, ['element::7']);

        static::assertNull($store->get('menu'));
    }

    #[Test]
    public function keepsAValueWhenAnUnrelatedTagIsInvalidated(): void
    {
        $cache = new ArrayCache();
        $store = new YiiTaggedStore($cache);
        $store->set('menu', value: 'html', tags: ['p::nav:main']);

        TagDependency::invalidate($cache, ['element::99']);

        static::assertEquals(new TaggedValue('html', ['p::nav:main']), $store->get('menu'));
    }

    #[Test]
    public function passesDurationAndNoDependencyForUntaggedValues(): void
    {
        $duration = 120;
        $cache    = $this->createMock(CacheInterface::class);
        $cache->expects(static::once())
            ->method('set')
            ->with('craftkit:menu', ['value' => 'html', 'tags' => []], $duration, static::isNull())
            ->willReturn(true);

        (new YiiTaggedStore($cache))->set('menu', value: 'html', tags: [], duration: $duration);
    }

    #[Test]
    public function returnsNullForAMissingKey(): void
    {
        static::assertNull((new YiiTaggedStore(new ArrayCache()))->get('menu'));
    }

    /**
     * @param mixed $entry
     */
    #[DataProvider('malformedEntryProvider')]
    #[Test]
    public function returnsNullForMalformedEntries(mixed $entry): void
    {
        $cache = new ArrayCache();
        $cache->set('craftkit:menu', $entry);

        static::assertNull((new YiiTaggedStore($cache))->get('menu'));
    }

    #[Test]
    public function storesUnderThePrefixedKey(): void
    {
        $cache = new ArrayCache();
        (new YiiTaggedStore($cache, 'pnav:'))->set('menu', value: 'html', tags: []);

        static::assertTrue($cache->exists('pnav:menu'));
    }
}
