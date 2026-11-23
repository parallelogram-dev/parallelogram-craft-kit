<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft\cp;

use craft\services\Users;
use craft\web\Application;
use craft\web\Request;
use craft\web\User as WebUser;
use Override;
use parallelogram\craftkit\craft\cp\AdvancedMode;
use parallelogram\craftkit\craft\cp\CraftPreferenceStore;
use parallelogram\craftkit\craft\cp\PreferenceStoreInterface;
use parallelogram\craftkit\craft\cp\UiController;
use parallelogram\craftkit\test\TestAsset\InMemoryPreferenceStore;
use parallelogram\craftkit\test\TestAsset\RunningApp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Throwable;
use yii\base\Module;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * The controller is built with a doubled request, a real response and an in-memory preference
 * store, and its action called directly; Yii's CSRF check and Craft's sign-in check run before
 * the action in a real request and aren't part of it.
 *
 * @internal
 */
#[CoversClass(UiController::class)]
#[UsesClass(AdvancedMode::class)]
#[UsesClass(CraftPreferenceStore::class)]
#[Group('unit')]
final class UiControllerTest extends TestCase
{
    /**
     * @throws Throwable
     */
    #[Test]
    public function answersWithTheSavedStateAsJson(): void
    {
        $response = $this->controller(
            new InMemoryPreferenceStore(admin: true),
            post: true,
            advanced: 'true',
        )->actionAdvanced();

        static::assertSame([Response::FORMAT_JSON, ['advanced' => true]], [$response->format, $response->data]);
    }

    /**
     * @throws Throwable
     */
    #[Test]
    public function readsTheSignedInUserWhenNoStoreIsGiven(): void
    {
        $webUser = $this->createStub(WebUser::class);
        $webUser->method('getIdentity')->willReturn(null);
        $app = $this->createStub(Application::class);
        $app->method('getUser')->willReturn($webUser);
        $app->method('getUsers')->willReturn($this->createStub(Users::class));
        RunningApp::use($app);

        $this->expectException(ForbiddenHttpException::class);

        $this->controller(null, post: true, advanced: '1')->actionAdvanced();
    }

    /**
     * @throws Throwable
     */
    #[Test]
    public function refusesEveryoneButAdmins(): void
    {
        $this->expectException(ForbiddenHttpException::class);
        $this->expectExceptionMessage('Only admins can switch Advanced mode.');

        $this->controller(new InMemoryPreferenceStore(admin: false), post: true, advanced: '1')->actionAdvanced();
    }

    /**
     * @throws Throwable
     */
    #[Test]
    public function refusesRequestsThatAreNotPosts(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Switching Advanced mode needs a POST request.');

        $this->controller(new InMemoryPreferenceStore(admin: true), post: false, advanced: '1')->actionAdvanced();
    }

    /**
     * @throws Throwable
     */
    #[Test]
    public function savesAdvancedModeForAnAdmin(): void
    {
        $store = new InMemoryPreferenceStore(admin: true);

        $this->controller($store, post: true, advanced: '1')->actionAdvanced();

        static::assertSame(['pkAdvancedMode' => true], $store->preferences);
    }

    /**
     * @throws Throwable
     */
    #[Test]
    public function savesSimpleModeForAnAdmin(): void
    {
        $store = new InMemoryPreferenceStore(
            admin: true,
            preferences: ['pkAdvancedMode' => true],
        );

        $this->controller($store, post: true, advanced: '0')->actionAdvanced();

        static::assertSame(['pkAdvancedMode' => false], $store->preferences);
    }

    #[Override]
    protected function tearDown(): void
    {
        RunningApp::clear();
    }

    private function controller(?PreferenceStoreInterface $store, bool $post, string $advanced): UiController
    {
        $request = $this->createStub(Request::class);
        $request->method('getIsPost')->willReturn($post);
        $request->method('getBodyParam')->willReturnMap([['advanced', null, $advanced]]);

        return new UiController('pk-ui', new Module('test'), [
            'request'  => $request,
            'response' => new Response(['charset' => 'UTF-8']),
            'store'    => $store,
        ]);
    }
}
