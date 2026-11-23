<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft\cp;

use craft\elements\User;
use craft\services\Users;
use craft\web\Application;
use craft\web\User as WebUser;
use Override;
use parallelogram\craftkit\craft\cp\CraftPreferenceStore;
use parallelogram\craftkit\test\TestAsset\RunningApp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Craft's User element and Users service are doubled, and the running application is stood in for
 * where the store reads it, so no Craft install is needed.
 *
 * @internal
 */
#[CoversClass(CraftPreferenceStore::class)]
#[Group('unit')]
final class CraftPreferenceStoreTest extends TestCase
{
    /**
     * @throws Throwable
     */
    #[Test]
    public function hasNothingToReadOrSaveWithoutAUser(): void
    {
        $users = $this->createMock(Users::class);
        $users->expects(static::never())->method('saveUserPreferences');
        $store = new CraftPreferenceStore(null, $users);
        $store->save('pkAdvancedMode', value: true);

        static::assertSame([false, null], [$store->isAdmin(), $store->preference('pkAdvancedMode')]);
    }

    #[Test]
    public function knowsAnAdmin(): void
    {
        static::assertTrue(
            (new CraftPreferenceStore($this->user(admin: true), $this->createStub(Users::class)))->isAdmin(),
        );
    }

    #[Test]
    public function knowsSomeoneWhoIsNotAnAdmin(): void
    {
        static::assertFalse(
            (new CraftPreferenceStore($this->user(admin: false), $this->createStub(Users::class)))->isAdmin(),
        );
    }

    /**
     * @throws Throwable
     */
    #[Test]
    public function readsNobodyWhenNobodyIsSignedIn(): void
    {
        $this->signIn(null);

        static::assertFalse(CraftPreferenceStore::current()->isAdmin());
    }

    #[Test]
    public function readsTheUsersPreference(): void
    {
        $user = $this->user(admin: true);
        $user->method('getPreference')->willReturnMap([['pkAdvancedMode', null, true]]);

        static::assertTrue((new CraftPreferenceStore($user, $this->createStub(Users::class)))->preference(
            'pkAdvancedMode',
        ));
    }

    /**
     * @throws Throwable
     */
    #[Test]
    public function readsWhoeverIsSignedIn(): void
    {
        $user = $this->user(admin: true);
        $this->signIn($user);

        static::assertTrue(CraftPreferenceStore::current()->isAdmin());
    }

    /**
     * @throws Throwable
     */
    #[Test]
    public function savesThePreferenceThroughCraft(): void
    {
        $user  = $this->user(admin: true);
        $users = $this->createMock(Users::class);
        $users->expects(static::once())->method('saveUserPreferences')->with($user, ['pkAdvancedMode' => true]);

        (new CraftPreferenceStore($user, $users))->save('pkAdvancedMode', value: true);
    }

    #[Override]
    protected function tearDown(): void
    {
        RunningApp::clear();
    }

    private function signIn(?User $identity): void
    {
        $webUser = $this->createStub(WebUser::class);
        $webUser->method('getIdentity')->willReturn($identity);
        $app = $this->createStub(Application::class);
        $app->method('getUser')->willReturn($webUser);
        $app->method('getUsers')->willReturn($this->createStub(Users::class));
        RunningApp::use($app);
    }

    /**
     * @return User&\PHPUnit\Framework\MockObject\Stub
     */
    private function user(bool $admin): User
    {
        $user        = $this->createStub(User::class);
        $user->admin = $admin;

        return $user;
    }
}
