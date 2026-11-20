<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft\cp;

use Craft;
use craft\elements\User;
use craft\services\Users;
use Override;
use Throwable;

/**
 * The signed-in user's preferences, through Craft's Users service.
 *
 * @api
 */
final readonly class CraftPreferenceStore implements PreferenceStoreInterface
{
    public function __construct(
        private ?User $user,
        private Users $users,
    ) {}

    /**
     * The store for whoever is signed in to the current request.
     *
     * @throws Throwable When Craft can't read the signed-in user.
     */
    public static function current(): self
    {
        $identity = Craft::$app->getUser()->getIdentity();

        return new self($identity instanceof User ? $identity : null, Craft::$app->getUsers());
    }

    #[Override]
    public function isAdmin(): bool
    {
        return true === $this->user?->admin;
    }

    #[Override]
    public function preference(string $key): mixed
    {
        return $this->user?->getPreference($key);
    }

    /**
     * @throws Throwable When Craft can't save the preferences.
     */
    #[Override]
    public function save(string $key, mixed $value): void
    {
        if (null !== $this->user) {
            $this->users->saveUserPreferences($this->user, [$key => $value]);
        }
    }
}
