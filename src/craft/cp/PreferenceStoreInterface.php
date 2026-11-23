<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft\cp;

/**
 * The signed-in user's preferences, as far as the CP UI kit needs them.
 *
 * @api
 */
interface PreferenceStoreInterface
{
    /**
     * Whether there is a signed-in admin.
     */
    public function isAdmin(): bool;

    /**
     * One of the user's preferences, or null when they have none or nobody is signed in.
     */
    public function preference(string $key): mixed;

    /**
     * Saves one of the user's preferences, keeping the rest. Does nothing when nobody is signed in.
     */
    public function save(string $key, mixed $value): void;
}
