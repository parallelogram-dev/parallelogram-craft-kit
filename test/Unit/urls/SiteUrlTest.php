<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\urls;

use DateTimeImmutable;
use InvalidArgumentException;
use parallelogram\craftkit\urls\SiteUrl;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SiteUrl::class)]
#[Group('unit')]
final class SiteUrlTest extends TestCase
{
    #[Test]
    public function keepsTheUrlAndLastModifiedDate(): void
    {
        $modified = new DateTimeImmutable('2026-11-18T10:00:00+10:00');
        $siteUrl  = new SiteUrl('https://example.test/about', $modified);

        static::assertSame('https://example.test/about', $siteUrl->url);
        static::assertSame($modified, $siteUrl->lastModified);
    }

    #[Test]
    public function rejectsAnEmptyUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A site URL cannot be empty.');

        new SiteUrl('');
    }
}
