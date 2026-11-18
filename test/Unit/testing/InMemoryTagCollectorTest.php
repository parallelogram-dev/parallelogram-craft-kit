<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\testing;

use LogicException;
use parallelogram\craftkit\cache\CollectedTags;
use parallelogram\craftkit\testing\InMemoryTagCollector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(InMemoryTagCollector::class)]
#[UsesClass(CollectedTags::class)]
#[Group('unit')]
final class InMemoryTagCollectorTest extends TestCase
{
    #[Test]
    public function closesTheOutermostCollection(): void
    {
        $collector = new InMemoryTagCollector();
        $collector->start();
        $collector->stop();

        static::assertFalse($collector->isCollecting());
    }

    #[Test]
    public function ignoresTagsWhenNoCollectionIsOpen(): void
    {
        $collector = new InMemoryTagCollector();
        $collector->collect(['element::1']);
        $collector->start();

        static::assertEquals(new CollectedTags([]), $collector->stop());
    }

    #[Test]
    public function mergesAnInnerCollectionIntoTheOuterOne(): void
    {
        $collector = new InMemoryTagCollector();
        $collector->start();
        $collector->collect(['element::1']);
        $collector->start();
        $collector->collect(['element::2', 'element::1']);

        static::assertSame(['element::2', 'element::1'], $collector->stop()->tags);
        static::assertSame(['element::1', 'element::2'], $collector->stop()->tags);
    }

    #[Test]
    public function refusesToStopWhenNothingIsOpen(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No tag collection is open.');

        (new InMemoryTagCollector())->stop();
    }

    #[Test]
    public function reportsAnOpenCollection(): void
    {
        $collector = new InMemoryTagCollector();
        $collector->start();

        static::assertTrue($collector->isCollecting());
    }
}
