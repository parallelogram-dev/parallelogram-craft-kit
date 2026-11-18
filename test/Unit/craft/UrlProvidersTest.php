<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft;

use Override;
use parallelogram\craftkit\craft\RegisterUrlProvidersEvent;
use parallelogram\craftkit\craft\UrlProviders;
use parallelogram\craftkit\testing\StaticUrlProvider;
use parallelogram\craftkit\urls\UrlProviderRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use yii\base\Event;

/**
 * @internal
 */
#[CoversClass(UrlProviders::class)]
#[CoversClass(RegisterUrlProvidersEvent::class)]
#[UsesClass(UrlProviderRegistry::class)]
#[UsesClass(StaticUrlProvider::class)]
#[Group('unit')]
final class UrlProvidersTest extends TestCase
{
    #[Test]
    public function collectsEveryRegisteredProviderInOrder(): void
    {
        $sitemap = new StaticUrlProvider([]);
        $extra   = new StaticUrlProvider([]);
        UrlProviders::register($sitemap);
        UrlProviders::register($extra);

        static::assertSame([$sitemap, $extra], UrlProviders::collect()->all());
    }

    #[Test]
    public function collectsNothingWhenNoProviderIsRegistered(): void
    {
        static::assertSame([], UrlProviders::collect()->all());
    }

    #[Override]
    protected function tearDown(): void
    {
        Event::offAll();
    }
}
