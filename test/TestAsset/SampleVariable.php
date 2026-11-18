<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\TestAsset;

/**
 * A plain class registered as a `craft.*` variable in tests.
 *
 * @internal
 */
final class SampleVariable
{
    public function hello(): string
    {
        return 'hello';
    }
}
