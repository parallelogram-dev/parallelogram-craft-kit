<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft;

use craft\events\InvalidateElementCachesEvent;
use craft\services\Elements;
use Override;
use parallelogram\craftkit\cache\TagList;
use parallelogram\craftkit\craft\CraftInvalidator;
use parallelogram\craftkit\craft\InvalidationEvents;
use parallelogram\craftkit\craft\TagsInvalidatedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use yii\base\Event;

/**
 * @internal
 */
#[CoversClass(InvalidationEvents::class)]
#[UsesClass(TagList::class)]
#[UsesClass(TagsInvalidatedEvent::class)]
#[Group('unit')]
final class InvalidationEventsTest extends TestCase
{
    /**
     * @var list<list<string>>
     */
    private array $received = [];

    #[Test]
    public function passesOnCraftElementInvalidations(): void
    {
        Event::trigger(
            Elements::class,
            Elements::EVENT_INVALIDATE_CACHES,
            new InvalidateElementCachesEvent(['tags' => ['element::7', 'element::craft\elements\Entry::section:2']]),
        );

        static::assertSame([['element::7', 'element::craft\elements\Entry::section:2']], $this->received);
    }

    #[Test]
    public function passesOnParallelogramInvalidations(): void
    {
        Event::trigger(
            CraftInvalidator::class,
            CraftInvalidator::EVENT_INVALIDATE,
            new TagsInvalidatedEvent(['tags' => ['p::nav:main']]),
        );

        static::assertSame([['p::nav:main']], $this->received);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->received = [];
        InvalidationEvents::listen(function (array $tags): void {
            $this->received[] = $tags;
        });
    }

    #[Override]
    protected function tearDown(): void
    {
        Event::offAll();
    }
}
