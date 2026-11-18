<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft;

use Closure;
use LogicException;
use Override;
use yii\base\Behavior;

use function array_values;

/**
 * Adds a single named method to the component it is attached to. The method
 * receives the owner as its first argument, followed by the call's arguments.
 *
 * @internal Attached by {@see ElementMethods}.
 *
 * @extends Behavior<\yii\base\Component>
 */
final class MethodBehavior extends Behavior
{
    public function __construct(
        private readonly string $methodName,
        private readonly Closure $method,
    ) {
        parent::__construct();
    }

    #[Override]
    public function hasMethod($name): bool
    {
        return $name === $this->methodName;
    }

    /**
     * @param array<array-key, mixed> $params
     *
     * @throws LogicException When the behavior is not attached.
     */
    #[Override]
    public function __call($name, $params): mixed
    {
        if ($name !== $this->methodName) {
            return parent::__call($name, $params);
        }

        if (null === $this->owner) {
            throw new LogicException("Method “{$name}” called on a detached behavior.");
        }

        return ($this->method)($this->owner, ...array_values($params));
    }
}
