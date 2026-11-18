<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft;

use craft\web\twig\variables\CraftVariable;
use Override;
use parallelogram\craftkit\craft\CraftVariables;
use parallelogram\craftkit\test\TestAsset\SampleVariable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * CraftVariable's constructor reads the running Craft application, so these
 * tests build one without it and raise its init event directly.
 *
 * @internal
 */
#[CoversClass(CraftVariables::class)]
#[Group('unit')]
final class CraftVariablesTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws InvalidConfigException
     */
    #[Test]
    public function addsTheVariableWhenCraftVariableInitialises(): void
    {
        CraftVariables::add('pSample', SampleVariable::class);
        $variable = (new ReflectionClass(CraftVariable::class))->newInstanceWithoutConstructor();

        Event::trigger(CraftVariable::class, CraftVariable::EVENT_INIT, new Event(['sender' => $variable]));

        static::assertInstanceOf(SampleVariable::class, $variable->get('pSample'));
    }

    #[Test]
    public function ignoresInitEventsFromOtherSenders(): void
    {
        CraftVariables::add('pSample', SampleVariable::class);
        $other = new SampleVariable();

        Event::trigger(CraftVariable::class, CraftVariable::EVENT_INIT, new Event(['sender' => $other]));

        static::assertSame('hello', $other->hello());
    }

    #[Override]
    protected function tearDown(): void
    {
        Event::offAll();
    }
}
