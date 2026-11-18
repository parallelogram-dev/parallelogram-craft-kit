<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\testing;

use parallelogram\craftkit\testing\RecordingInvalidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RecordingInvalidator::class)]
#[Group('unit')]
final class RecordingInvalidatorTest extends TestCase
{
    #[Test]
    public function recordsEachCallInOrder(): void
    {
        $invalidator = new RecordingInvalidator();
        $invalidator->invalidate(['p::nav:main']);
        $invalidator->invalidate(['element::7', 'p::all']);

        static::assertSame([['p::nav:main'], ['element::7', 'p::all']], $invalidator->calls());
    }
}
