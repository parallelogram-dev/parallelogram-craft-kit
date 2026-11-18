<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\cache;

use craft\elements\Entry;
use InvalidArgumentException;
use parallelogram\craftkit\cache\Tag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Tag::class)]
#[Group('unit')]
final class TagTest extends TestCase
{
    /**
     * @return array<string, array{string, list<string|int>, string}>
     */
    public static function customTagProvider(): array
    {
        return [
            'scope only'          => ['all', [], 'p::all'],
            'scope and one part'  => ['nav', ['main'], 'p::nav:main'],
            'scope and int parts' => ['seo', ['profile', 4], 'p::seo:profile:4'],
        ];
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function isCustomProvider(): array
    {
        return [
            'custom tag'    => ['p::nav:main', true],
            'element tag'   => ['element::12', false],
            'similar start' => ['p:nav', false],
        ];
    }

    #[Test]
    public function buildsCraftElementTag(): void
    {
        static::assertSame('element::12', Tag::element(12));
    }

    #[Test]
    public function buildsCraftElementTypeTag(): void
    {
        static::assertSame('element::craft\elements\Entry', Tag::elementType(Entry::class));
    }

    /**
     * @param list<string|int> $parts
     */
    #[DataProvider('customTagProvider')]
    #[Test]
    public function buildsCustomTagsWithThePrefix(string $scope, array $parts, string $expected): void
    {
        static::assertSame($expected, Tag::custom($scope, ...$parts));
    }

    #[Test]
    public function buildsSiteTag(): void
    {
        static::assertSame('p::site:3', Tag::site(3));
    }

    #[DataProvider('isCustomProvider')]
    #[Test]
    public function recognisesCustomTags(string $tag, bool $expected): void
    {
        static::assertSame($expected, Tag::isCustom($tag));
    }

    #[Test]
    public function rejectsCustomTagWithoutScope(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A custom tag needs a scope.');

        Tag::custom('');
    }
}
