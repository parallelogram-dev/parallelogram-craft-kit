<?php

declare(strict_types=1);

namespace parallelogram\craftkit\cache;

use InvalidArgumentException;

use function implode;
use function str_starts_with;

/**
 * The cache-tag vocabulary shared by Parallelogram plugins.
 *
 * Element tags reuse Craft's own format (`element::{id}`, `element::{class}`),
 * so anything cached through Craft's tag collection is invalidated by Craft's
 * normal element saves. Tags for things that are not elements (menus, SEO
 * values, root files) carry the `p::` prefix.
 *
 * @api
 */
final class Tag
{
    public const string PREFIX = 'p::';

    /**
     * Matches every page cached by a Parallelogram plugin.
     */
    public const string ALL = self::PREFIX . 'all';

    /**
     * A tag for something that is not an element, e.g. `custom('nav', 'main')`
     * gives `p::nav:main`.
     *
     * @throws InvalidArgumentException When the scope is empty.
     */
    public static function custom(string $scope, string|int ...$parts): string
    {
        if ('' === $scope) {
            throw new InvalidArgumentException('A custom tag needs a scope.');
        }

        return self::PREFIX . implode(':', [$scope, ...$parts]);
    }

    /**
     * Craft's tag for a single element.
     */
    public static function element(int $elementId): string
    {
        return "element::{$elementId}";
    }

    /**
     * Craft's tag for every element of a type.
     *
     * @param class-string $elementType
     */
    public static function elementType(string $elementType): string
    {
        return "element::{$elementType}";
    }

    public static function isCustom(string $tag): bool
    {
        return str_starts_with($tag, self::PREFIX);
    }

    /**
     * Matches every page cached for one site.
     */
    public static function site(int $siteId): string
    {
        return self::custom('site', $siteId);
    }
}
