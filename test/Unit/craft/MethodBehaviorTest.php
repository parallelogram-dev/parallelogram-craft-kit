<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft;

use LogicException;
use parallelogram\craftkit\craft\MethodBehavior;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use yii\base\UnknownMethodException;

/**
 * @internal
 */
#[CoversClass(MethodBehavior::class)]
#[Group('unit')]
final class MethodBehaviorTest extends TestCase
{
    #[Test]
    public function claimsOnlyItsOwnMethod(): void
    {
        $behavior = new MethodBehavior('picture', static fn(): null => null);

        static::assertSame([true, false], [$behavior->hasMethod('picture'), $behavior->hasMethod('variants')]);
    }

    #[Test]
    public function refusesACallWhenDetached(): void
    {
        $behavior = new MethodBehavior('picture', static fn(): null => null);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Method “picture” called on a detached behavior.');

        $behavior->__call('picture', []);
    }

    #[Test]
    public function refusesOtherMethodNames(): void
    {
        $behavior = new MethodBehavior('picture', static fn(): null => null);

        $this->expectException(UnknownMethodException::class);

        $behavior->__call('variants', []);
    }
}
