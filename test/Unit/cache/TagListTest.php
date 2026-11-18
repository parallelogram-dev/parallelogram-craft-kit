<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\cache;

use parallelogram\craftkit\cache\TagList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TagList::class)]
#[Group('unit')]
final class TagListTest extends TestCase
{
    /**
     * @return array<string, array{mixed, list<string>}>
     */
    public static function tagsProvider(): array
    {
        return [
            'single string'   => ['element::5', ['element::5']],
            'list of strings' => [['a', 'b'], ['a', 'b']],
            'keyed array'     => [['x' => 'a', 'y' => 'b'], ['a', 'b']],
            'mixed array'     => [['a', 5, null, 'b'], ['a', 'b']],
            'null'            => [null, []],
            'integer'         => [7, []],
        ];
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('tagsProvider')]
    #[Test]
    public function normalisesLooseTagValuesToAListOfStrings(mixed $tags, array $expected): void
    {
        static::assertSame($expected, TagList::from($tags));
    }
}
