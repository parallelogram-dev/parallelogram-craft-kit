<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\testing;

use parallelogram\craftkit\cache\TaggedValue;
use parallelogram\craftkit\testing\InMemoryTaggedStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(InMemoryTaggedStore::class)]
#[UsesClass(TaggedValue::class)]
#[Group('unit')]
final class InMemoryTaggedStoreTest extends TestCase
{
    #[Test]
    public function forgetsTheDurationOfAnInvalidatedValue(): void
    {
        $store = new InMemoryTaggedStore();
        $store->set('menu', value: 'html', tags: ['p::nav:main'], duration: 60);
        $store->invalidate(['p::nav:main']);

        static::assertNull($store->durationOf('menu'));
    }

    #[Test]
    public function invalidatesOnlyValuesSharingATag(): void
    {
        $store = new InMemoryTaggedStore();
        $store->set('main', value: 'a', tags: ['p::nav:main']);
        $store->set('footer', value: 'b', tags: ['p::nav:footer']);

        $store->invalidate(['p::nav:main']);

        static::assertSame([null, 'b'], [$store->get('main'), $store->get('footer')?->value]);
    }

    #[Test]
    public function remembersTheDuration(): void
    {
        $store = new InMemoryTaggedStore();
        $store->set('menu', value: 'html', tags: [], duration: 60);

        static::assertSame(60, $store->durationOf('menu'));
    }

    #[Test]
    public function returnsWhatWasStored(): void
    {
        $store = new InMemoryTaggedStore();
        $store->set('menu', value: 'html', tags: ['p::nav:main']);

        static::assertEquals(new TaggedValue('html', ['p::nav:main']), $store->get('menu'));
    }
}
