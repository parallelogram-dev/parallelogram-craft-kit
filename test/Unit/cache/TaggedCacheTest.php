<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\cache;

use Override;
use parallelogram\craftkit\cache\CollectedTags;
use parallelogram\craftkit\cache\TagCollectorInterface;
use parallelogram\craftkit\cache\TaggedCache;
use parallelogram\craftkit\cache\TaggedValue;
use parallelogram\craftkit\test\TestAsset\FailingProducer;
use parallelogram\craftkit\testing\InMemoryTagCollector;
use parallelogram\craftkit\testing\InMemoryTaggedStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
#[CoversClass(TaggedCache::class)]
#[CoversClass(CollectedTags::class)]
#[CoversClass(TaggedValue::class)]
#[UsesClass(InMemoryTagCollector::class)]
#[UsesClass(InMemoryTaggedStore::class)]
#[Group('unit')]
final class TaggedCacheTest extends TestCase
{
    private InMemoryTagCollector $collector;

    private InMemoryTaggedStore $store;

    private TaggedCache $cache;

    #[Test]
    public function doesNotCallTheProducerOnAHit(): void
    {
        $this->store->set('menu', value: 'cached', tags: ['p::nav:main']);

        $value = $this->cache->remember(
            'menu',
            new FailingProducer('Producer should not run.'),
        );

        static::assertSame('cached', $value);
    }

    #[Test]
    public function leavesNoCollectionOpenWhenTheProducerThrows(): void
    {
        try {
            $this->cache->remember('menu', new FailingProducer());
            static::fail('The producer exception should propagate.');
        } catch (RuntimeException $exception) {
            static::assertSame('Failed to build.', $exception->getMessage());
        }

        static::assertFalse($this->collector->isCollecting());
    }

    #[Test]
    public function mergesTagsFromAFreshlyProducedValueIntoTheEnclosingCollection(): void
    {
        $this->collector->start();
        $this->collector->collect(['element::1']);

        $this->cache->remember('menu', function (): string {
            $this->collector->collect(['element::7']);

            return 'menu html';
        });

        static::assertSame(['element::1', 'element::7'], $this->collector->stop()->tags);
    }

    #[Test]
    public function passesTheCollectedDurationToTheStore(): void
    {
        $collector = $this->createStub(TagCollectorInterface::class);
        $collector->method('stop')->willReturn(new CollectedTags(['element::7'], 300));

        (new TaggedCache($this->store, $collector))->remember('menu', static fn(): string => 'menu html');

        static::assertSame(300, $this->store->durationOf('menu'));
    }

    #[Test]
    public function replaysStoredTagsIntoTheEnclosingCollectionOnAHit(): void
    {
        $this->store->set('menu', value: 'cached', tags: ['p::nav:main', 'element::7']);
        $this->collector->start();

        $this->cache->remember('menu', static fn(): string => 'fresh');

        static::assertSame(['p::nav:main', 'element::7'], $this->collector->stop()->tags);
    }

    #[Test]
    public function returnsAndStoresAFreshValueOnAMiss(): void
    {
        $value = $this->cache->remember('menu', function (): string {
            $this->collector->collect(['element::7']);

            return 'menu html';
        });

        static::assertSame('menu html', $value);
        static::assertEquals(new TaggedValue('menu html', ['element::7']), $this->store->get('menu'));
    }

    #[Test]
    public function storesNothingWhenTheProducerThrows(): void
    {
        try {
            $this->cache->remember('menu', new FailingProducer());
            static::fail('The producer exception should propagate.');
        } catch (RuntimeException $exception) {
            static::assertSame('Failed to build.', $exception->getMessage());
        }

        static::assertNull($this->store->get('menu'));
    }

    #[Override]
    protected function setUp(): void
    {
        $this->collector = new InMemoryTagCollector();
        $this->store     = new InMemoryTaggedStore();
        $this->cache     = new TaggedCache($this->store, $this->collector);
    }
}
