<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft;

use Closure;
use craft\events\InvalidateElementCachesEvent;
use craft\services\Elements;
use parallelogram\craftkit\cache\TagList;
use yii\base\Event;

/**
 * One hook for every tag invalidation, whether Craft raised it for a saved
 * element or a Parallelogram plugin raised it through {@see CraftInvalidator}.
 *
 * @api
 */
final class InvalidationEvents
{
    /**
     * @param Closure(list<string>): void $handler
     */
    public static function listen(Closure $handler): void
    {
        Event::on(
            Elements::class,
            Elements::EVENT_INVALIDATE_CACHES,
            static function (InvalidateElementCachesEvent $event) use ($handler): void {
                $handler(TagList::from($event->tags));
            },
        );

        Event::on(
            CraftInvalidator::class,
            CraftInvalidator::EVENT_INVALIDATE,
            static function (TagsInvalidatedEvent $event) use ($handler): void {
                $handler($event->tags);
            },
        );
    }
}
