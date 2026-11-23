<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\TestAsset;

use Override;
use parallelogram\craftkit\craft\cp\PreferenceStoreInterface;

/**
 * A signed-in user's preferences, held in memory.
 *
 * @internal
 */
final class InMemoryPreferenceStore implements PreferenceStoreInterface
{
    /**
     * @param array<string, mixed> $preferences
     */
    public function __construct(
        private readonly bool $admin,
        public array $preferences = [],
    ) {}

    #[Override]
    public function isAdmin(): bool
    {
        return $this->admin;
    }

    #[Override]
    public function preference(string $key): mixed
    {
        return $this->preferences[$key] ?? null;
    }

    #[Override]
    public function save(string $key, mixed $value): void
    {
        $this->preferences[$key] = $value;
    }
}
