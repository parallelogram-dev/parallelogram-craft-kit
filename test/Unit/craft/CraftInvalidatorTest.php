<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft;

use Override;
use parallelogram\craftkit\cache\TaggedValue;
use parallelogram\craftkit\cache\TagList;
use parallelogram\craftkit\craft\CraftInvalidator;
use parallelogram\craftkit\craft\TagsInvalidatedEvent;
use parallelogram\craftkit\craft\YiiTaggedStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use yii\base\Event;
use yii\caching\ArrayCache;

/**
 * @internal
 */
#[CoversClass(CraftInvalidator::class)]
#[CoversClass(TagsInvalidatedEvent::class)]
#[UsesClass(YiiTaggedStore::class)]
#[UsesClass(TaggedValue::class)]
#[UsesClass(TagList::class)]
#[Group('unit')]
final class CraftInvalidatorTest extends TestCase
{
    /**
     * @var list<list<string>>
     */
    private array $announced = [];

    #[Test]
    public function announcesTheInvalidatedTags(): void
    {
        (new CraftInvalidator(new ArrayCache()))->invalidate(['p::nav:main']);

        static::assertSame([['p::nav:main']], $this->announced);
    }

    #[Test]
    public function doesNothingForAnEmptyTagList(): void
    {
        (new CraftInvalidator(new ArrayCache()))->invalidate([]);

        static::assertSame([], $this->announced);
    }

    #[Test]
    public function invalidatesValuesStoredUnderTheTags(): void
    {
        $cache = new ArrayCache();
        $store = new YiiTaggedStore($cache);
        $store->set('menu', value: 'html', tags: ['p::nav:main']);

        (new CraftInvalidator($cache))->invalidate(['p::nav:main']);

        static::assertNull($store->get('menu'));
    }

    #[Override]
    protected function setUp(): void
    {
        $this->announced = [];
        Event::on(
            CraftInvalidator::class,
            CraftInvalidator::EVENT_INVALIDATE,
            function (TagsInvalidatedEvent $event): void {
                $this->announced[] = $event->tags;
            },
        );
    }

    #[Override]
    protected function tearDown(): void
    {
        Event::offAll();
    }
}
