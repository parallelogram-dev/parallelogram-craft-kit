<?php

declare(strict_types=1);

namespace parallelogram\craftkit\cache;

use function array_filter;
use function array_values;
use function is_array;
use function is_string;

/**
 * Normalises the loosely typed tag lists Craft and Yii hand around (a single
 * string, or an array that may hold anything) into a list of strings.
 *
 * @internal
 */
final class TagList
{
    /**
     * @return list<string>
     */
    public static function from(mixed $tags): array
    {
        if (is_string($tags)) {
            return [$tags];
        }

        if (! is_array($tags)) {
            return [];
        }

        return array_values(array_filter($tags, is_string(...)));
    }
}
