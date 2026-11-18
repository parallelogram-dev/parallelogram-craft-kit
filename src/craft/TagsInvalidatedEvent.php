<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft;

use yii\base\Event;

/**
 * Raised when a Parallelogram plugin invalidates tags that Craft itself does
 * not know about, such as `p::nav:main`.
 *
 * @api
 */
final class TagsInvalidatedEvent extends Event
{
    /**
     * @var list<string>
     */
    public array $tags = [];
}
