<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft\cp;

use function in_array;
use function is_string;
use function strtolower;
use function trim;

/**
 * Whether a user sees the Advanced parts of a Parallelogram plugin's pages.
 *
 * Only admins ever get Advanced mode; for anyone else it is off whatever their preference says. The
 * preference is shared by every plugin that uses the kit, so switching it in one switches it in all.
 *
 * @api
 */
final readonly class AdvancedMode
{
    /**
     * The user preference the switch is saved under.
     */
    public const string PREFERENCE = 'pkAdvancedMode';

    private function __construct(
        public bool $available,
        public bool $on,
    ) {}

    /**
     * @param mixed $preference The stored preference: Craft keeps preferences as JSON, so a boolean, a number or a string.
     */
    public static function for(bool $admin, mixed $preference): self
    {
        return new self($admin, $admin && self::isOn($preference));
    }

    /**
     * Whether a stored or posted value means "on".
     */
    public static function isOn(mixed $value): bool
    {
        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], strict: true);
        }

        return true === $value || 1 === $value;
    }
}
