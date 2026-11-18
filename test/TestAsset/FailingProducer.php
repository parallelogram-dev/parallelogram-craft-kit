<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\TestAsset;

use RuntimeException;

/**
 * A producer that always fails, for checking cleanup when producing a value
 * throws.
 *
 * @internal
 */
final readonly class FailingProducer
{
    public function __construct(
        private string $message = 'Failed to build.',
    ) {}

    /**
     * @throws RuntimeException Always.
     */
    public function __invoke(): string
    {
        throw new RuntimeException($this->message);
    }
}
