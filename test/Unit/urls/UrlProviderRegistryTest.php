<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\urls;

use DateTimeImmutable;
use parallelogram\craftkit\testing\StaticUrlProvider;
use parallelogram\craftkit\urls\SiteUrl;
use parallelogram\craftkit\urls\UrlProviderRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UrlProviderRegistry::class)]
#[UsesClass(SiteUrl::class)]
#[UsesClass(StaticUrlProvider::class)]
#[Group('unit')]
final class UrlProviderRegistryTest extends TestCase
{
    #[Test]
    public function keepsTheFirstProvidersEntryForADuplicateUrl(): void
    {
        $first    = new SiteUrl('https://example.test/', new DateTimeImmutable('2026-11-01'));
        $registry = new UrlProviderRegistry();
        $registry->add(new StaticUrlProvider([1 => [$first]]));
        $registry->add(new StaticUrlProvider([1 => [new SiteUrl('https://example.test/')]]));

        static::assertSame([$first], $registry->urlsForSite(1));
    }

    #[Test]
    public function listsProvidersInRegistrationOrder(): void
    {
        $first    = new StaticUrlProvider([]);
        $second   = new StaticUrlProvider([]);
        $registry = new UrlProviderRegistry();
        $registry->add($first);
        $registry->add($second);

        static::assertSame([$first, $second], $registry->all());
    }

    #[Test]
    public function yieldsNothingForASiteNoProviderKnows(): void
    {
        $registry = new UrlProviderRegistry();
        $registry->add(new StaticUrlProvider([1 => [new SiteUrl('https://example.test/')]]));

        static::assertSame([], $registry->urlsForSite(2));
    }

    #[Test]
    public function yieldsNothingWithoutProviders(): void
    {
        static::assertSame([], (new UrlProviderRegistry())->urlsForSite(1));
    }

    #[Test]
    public function yieldsUrlsFromEveryProviderInOrder(): void
    {
        $home     = new SiteUrl('https://example.test/');
        $about    = new SiteUrl('https://example.test/about');
        $registry = new UrlProviderRegistry();
        $registry->add(new StaticUrlProvider([1 => [$home]]));
        $registry->add(new StaticUrlProvider([1 => [$about]]));

        static::assertSame([$home, $about], $registry->urlsForSite(1));
    }
}
