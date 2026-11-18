<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft;

use craft\services\Elements;
use parallelogram\craftkit\cache\CollectedTags;
use parallelogram\craftkit\cache\TagList;
use parallelogram\craftkit\craft\CraftTagCollector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use yii\caching\TagDependency;

/**
 * @internal
 */
#[CoversClass(CraftTagCollector::class)]
#[UsesClass(CollectedTags::class)]
#[UsesClass(TagList::class)]
#[Group('unit')]
final class CraftTagCollectorTest extends TestCase
{
    #[Test]
    public function forwardsCollectedTagsToCraft(): void
    {
        $elements = $this->createMock(Elements::class);
        $elements->expects(static::once())->method('collectCacheTags')->with(['p::nav:main']);

        (new CraftTagCollector($elements))->collect(['p::nav:main']);
    }

    #[Test]
    public function reportsWhetherCraftIsCollecting(): void
    {
        $elements = $this->createStub(Elements::class);
        $elements->method('getIsCollectingCacheInfo')->willReturn(true);

        static::assertTrue((new CraftTagCollector($elements))->isCollecting());
    }

    #[Test]
    public function returnsNoTagsWhenCraftCollectedNone(): void
    {
        $elements = $this->createStub(Elements::class);
        $elements->method('stopCollectingCacheInfo')->willReturn([null, null]);

        static::assertEquals(new CollectedTags([]), (new CraftTagCollector($elements))->stop());
    }

    #[Test]
    public function returnsTheTagsAndDurationCraftCollected(): void
    {
        $elements = $this->createStub(Elements::class);
        $elements->method('stopCollectingCacheInfo')
            ->willReturn([new TagDependency(['tags' => ['element', 'element::7']]), 600]);

        static::assertEquals(
            new CollectedTags(['element', 'element::7'], 600),
            (new CraftTagCollector($elements))->stop(),
        );
    }

    #[Test]
    public function startsCraftsCollection(): void
    {
        $elements = $this->createMock(Elements::class);
        $elements->expects(static::once())->method('startCollectingCacheInfo');

        (new CraftTagCollector($elements))->start();
    }
}
