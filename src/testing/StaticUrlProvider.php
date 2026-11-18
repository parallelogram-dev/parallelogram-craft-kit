<?php

declare(strict_types=1);

namespace parallelogram\craftkit\testing;

use Override;
use parallelogram\craftkit\urls\SiteUrl;
use parallelogram\craftkit\urls\UrlProviderInterface;

/**
 * A URL provider for tests that returns a fixed list per site.
 *
 * @api
 */
final readonly class StaticUrlProvider implements UrlProviderInterface
{
    /**
     * @param array<int, list<SiteUrl>> $urlsBySite
     */
    public function __construct(
        private array $urlsBySite,
    ) {}

    #[Override]
    public function urls(int $siteId): iterable
    {
        return $this->urlsBySite[$siteId] ?? [];
    }
}
