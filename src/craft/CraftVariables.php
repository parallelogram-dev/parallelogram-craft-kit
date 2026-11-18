<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft;

use Closure;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Adds a variable under `craft.*` in Twig, e.g. `craft.pNav`.
 *
 * On Craft 5 this sets a component when the variable initialises; the Craft 6
 * release of craft-kit does the same through a CraftVariable macro.
 *
 * @api
 */
final class CraftVariables
{
    /**
     * @param class-string|array<string, mixed>|Closure $definition Anything Yii's service locator accepts.
     */
    public static function add(string $name, string|array|Closure $definition): void
    {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            /**
             * @throws InvalidConfigException When the definition is not one the service locator accepts.
             */
            static function (Event $event) use ($name, $definition): void {
                $variable = $event->sender;

                if ($variable instanceof CraftVariable) {
                    $variable->set($name, $definition);
                }
            },
        );
    }
}
