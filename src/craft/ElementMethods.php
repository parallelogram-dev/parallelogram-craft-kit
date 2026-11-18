<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft;

use Closure;
use craft\base\Model;
use craft\events\DefineBehaviorsEvent;
use InvalidArgumentException;
use yii\base\Event;

use function is_a;
use function preg_match;

/**
 * Adds methods to elements (and other Craft models), e.g. `asset.picture()`.
 *
 * On Craft 5 this attaches a behavior; the Craft 6 release of craft-kit does
 * the same through Laravel macros. Either way the method receives the element
 * as its first argument, so plugin code is identical on both.
 *
 * @api
 */
final class ElementMethods
{
    /**
     * @param class-string $class A Craft model or element class, e.g. Asset::class.
     * @param Closure $method Receives the model, then the call's arguments.
     *
     * @throws InvalidArgumentException When the class is not a Craft model or the name is not a valid method name.
     */
    public static function add(string $class, string $name, Closure $method): void
    {
        if (! is_a($class, Model::class, allow_string: true)) {
            throw new InvalidArgumentException("“{$class}” is not a Craft model, so methods can't be added to it.");
        }

        if (1 !== preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new InvalidArgumentException("“{$name}” is not a valid method name.");
        }

        Event::on(
            $class,
            Model::EVENT_DEFINE_BEHAVIORS,
            static function (DefineBehaviorsEvent $event) use ($name, $method): void {
                $event->behaviors["craftkit.method.{$name}"] = new MethodBehavior($name, $method);
            },
        );
    }
}
