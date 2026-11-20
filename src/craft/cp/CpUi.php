<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft\cp;

use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use yii\base\Event;
use yii\base\Module;

/**
 * Sets a plugin up for the CP UI kit: the `_pk` template root (`{% import '_pk/macros' as pk %}`)
 * and the `pk-ui` controller that saves the Simple / Advanced switch. Call it from the plugin's
 * `init()`; calling it again, or from several plugins, does no harm.
 *
 * @api
 */
final class CpUi
{
    /**
     * The template root the kit's macros live under.
     */
    public const string TEMPLATE_ROOT = '_pk';

    /**
     * The controller ID the kit adds to each plugin.
     */
    public const string CONTROLLER = 'pk-ui';

    public static function addTemplateRoot(RegisterTemplateRootsEvent $event): void
    {
        $event->roots[self::TEMPLATE_ROOT] = self::templatePath();
    }

    /**
     * The action path that saves the Simple / Advanced switch for a plugin.
     */
    public static function advancedAction(string $pluginHandle): string
    {
        return "{$pluginHandle}/" . self::CONTROLLER . '/advanced';
    }

    public static function register(Module $plugin): void
    {
        $plugin->controllerMap[self::CONTROLLER] ??= UiController::class;

        $handler = [self::class, 'addTemplateRoot'];
        Event::off(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, $handler);
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, $handler);
    }

    /**
     * The folder holding the kit's templates.
     */
    public static function templatePath(): string
    {
        return __DIR__ . '/templates';
    }
}
