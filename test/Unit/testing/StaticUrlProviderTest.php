<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\testing;

use parallelogram\craftkit\testing\StaticUrlProvider;
use parallelogram\craftkit\urls\SiteUrl;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(StaticUrlProvider::class)]
#[UsesClass(SiteUrl::class)]
#[Group('unit')]
final class StaticUrlProviderTest extends TestCase
{
    #[Test]
    public function returnsNothingForAnUnknownSite(): void
    {
        static::assertSame([], (new StaticUrlProvider([]))->urls(1));
    }

    #[Test]
    public function returnsTheUrlsGivenForASite(): void
    {
        $home = new SiteUrl('https://example.test/');

        static::assertSame([$home], (new StaticUrlProvider([1 => [$home]]))->urls(1));
    }
}
