<?php

declare(strict_types=1);

namespace parallelogram\craftkit\urls;

use function array_key_exists;
use function array_values;

/**
 * Every URL provider registered on a site, combined into one de-duplicated
 * list.
 *
 * @api
 */
final class UrlProviderRegistry
{
    /**
     * @var list<UrlProviderInterface>
     */
    private array $providers = [];

    public function add(UrlProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * @return list<UrlProviderInterface>
     */
    public function all(): array
    {
        return $this->providers;
    }

    /**
     * Every URL once, in provider order. When two providers list the same URL,
     * the first one wins.
     *
     * @return list<SiteUrl>
     */
    public function urlsForSite(int $siteId): array
    {
        $urls = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->urls($siteId) as $siteUrl) {
                if (array_key_exists($siteUrl->url, $urls)) {
                    continue;
                }

                $urls[$siteUrl->url] = $siteUrl;
            }
        }

        return array_values($urls);
    }
}
