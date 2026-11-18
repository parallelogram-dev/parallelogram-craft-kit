<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft;

use InvalidArgumentException;
use Override;
use parallelogram\craftkit\craft\ElementMethods;
use parallelogram\craftkit\craft\MethodBehavior;
use parallelogram\craftkit\test\TestAsset\SampleModel;
use parallelogram\craftkit\test\TestAsset\SampleVariable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use yii\base\Event;
use yii\base\UnknownMethodException;

/**
 * @internal
 */
#[CoversClass(ElementMethods::class)]
#[CoversClass(MethodBehavior::class)]
#[Group('unit')]
final class ElementMethodsTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function invalidNameProvider(): array
    {
        return [
            'empty'           => [''],
            'leading digit'   => ['2picture'],
            'contains a dash' => ['my-picture'],
        ];
    }

    #[Test]
    public function addedMethodReceivesTheModelAndArguments(): void
    {
        ElementMethods::add(
            SampleModel::class,
            name: 'describe',
            method: static fn(SampleModel $model, string $suffix): string => "{$model->name}-{$suffix}",
        );

        static::assertSame('sample-large', (new SampleModel())->describe('large'));
    }

    #[Test]
    public function keepsSeveralAddedMethodsSeparate(): void
    {
        ElementMethods::add(SampleModel::class, name: 'first', method: static fn(): string => 'one');
        ElementMethods::add(SampleModel::class, name: 'second', method: static fn(): string => 'two');

        $model = new SampleModel();

        static::assertSame(['one', 'two'], [$model->first(), $model->second()]);
    }

    #[Test]
    public function rejectsAClassThatIsNotACraftModel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is not a Craft model');

        ElementMethods::add(SampleVariable::class, name: 'picture', method: static fn(): null => null);
    }

    /**
     * @param string $name
     */
    #[DataProvider('invalidNameProvider')]
    #[Test]
    public function rejectsAnInvalidMethodName(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("“{$name}” is not a valid method name.");

        ElementMethods::add(SampleModel::class, $name, static fn(): null => null);
    }

    #[Test]
    public function stillRejectsMethodsThatWereNeverAdded(): void
    {
        ElementMethods::add(SampleModel::class, name: 'describe', method: static fn(): string => 'x');

        $this->expectException(UnknownMethodException::class);

        (new SampleModel())->unknown();
    }

    #[Override]
    protected function tearDown(): void
    {
        Event::offAll();
    }
}
