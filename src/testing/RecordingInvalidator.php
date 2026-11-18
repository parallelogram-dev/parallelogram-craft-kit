<?php

declare(strict_types=1);

namespace parallelogram\craftkit\testing;

use Override;
use parallelogram\craftkit\cache\InvalidatorInterface;

/**
 * An invalidator for tests that only records what it was asked to invalidate.
 *
 * @api
 */
final class RecordingInvalidator implements InvalidatorInterface
{
    /**
     * @var list<list<string>>
     */
    private array $calls = [];

    /**
     * Each call's tags, in call order.
     *
     * @return list<list<string>>
     */
    public function calls(): array
    {
        return $this->calls;
    }

    #[Override]
    public function invalidate(array $tags): void
    {
        $this->calls[] = $tags;
    }
}
