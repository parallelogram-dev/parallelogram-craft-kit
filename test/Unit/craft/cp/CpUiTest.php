<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft\cp;

use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use Override;
use parallelogram\craftkit\craft\cp\CpUi;
use parallelogram\craftkit\craft\cp\UiController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use yii\base\Event;
use yii\base\Module;

use function is_file;

/**
 * @internal
 */
#[CoversClass(CpUi::class)]
#[Group('unit')]
final class CpUiTest extends TestCase
{
    #[Test]
    public function addsTheKitsControllerToThePlugin(): void
    {
        $plugin = new Module('p-test');

        CpUi::register($plugin);

        static::assertSame(UiController::class, $plugin->controllerMap['pk-ui'] ?? null);
    }

    #[Test]
    public function addsTheTemplateRootOnceHoweverManyPluginsRegister(): void
    {
        CpUi::register(new Module('p-one'));
        CpUi::register(new Module('p-two'));
        $handler = [CpUi::class, 'addTemplateRoot'];

        Event::off(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, $handler);

        static::assertFalse(Event::hasHandlers(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS));
    }

    #[Test]
    public function addsTheTemplateRootToTheControlPanel(): void
    {
        CpUi::register(new Module('p-test'));
        $event = new RegisterTemplateRootsEvent();

        Event::trigger(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, $event);

        static::assertSame(['_pk' => CpUi::templatePath()], $event->roots);
    }

    #[Test]
    public function keepsTheMacrosInTheTemplateRoot(): void
    {
        static::assertSame(
            [true, true],
            [is_file(CpUi::templatePath() . '/macros.twig'), is_file(CpUi::templatePath() . '/modal.twig')],
        );
    }

    #[Test]
    public function leavesAControllerThePluginAlreadyMapped(): void
    {
        $plugin                         = new Module('p-test');
        $plugin->controllerMap['pk-ui'] = 'my\\Controller';

        CpUi::register($plugin);

        static::assertSame('my\\Controller', $plugin->controllerMap['pk-ui'] ?? null);
    }

    #[Test]
    public function namesTheActionThatSavesAdvancedMode(): void
    {
        static::assertSame('p-nav/pk-ui/advanced', CpUi::advancedAction('p-nav'));
    }

    #[Override]
    protected function tearDown(): void
    {
        Event::offAll();
    }
}
