<?php

declare(strict_types=1);

namespace parallelogram\craftkit\urls;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * One public URL of a site, with when its content last changed if known.
 *
 * @api
 */
final readonly class SiteUrl
{
    /**
     * @throws InvalidArgumentException When the URL is empty.
     */
    public function __construct(
        public string $url,
        public ?DateTimeImmutable $lastModified = null,
    ) {
        if ('' === $url) {
            throw new InvalidArgumentException('A site URL cannot be empty.');
        }
    }
}
