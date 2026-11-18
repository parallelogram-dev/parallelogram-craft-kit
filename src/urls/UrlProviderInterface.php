<?php

declare(strict_types=1);

namespace parallelogram\craftkit\urls;

/**
 * Lists the public URLs a plugin knows about for a site. Parallelogram SEO
 * provides its sitemap URLs this way, and Parallelogram Cache warms them.
 *
 * @api
 */
interface UrlProviderInterface
{
    /**
     * @return iterable<SiteUrl>
     */
    public function urls(int $siteId): iterable;
}
