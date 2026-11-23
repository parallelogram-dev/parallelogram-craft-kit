<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft\cp;

use parallelogram\craftkit\craft\cp\AdvancedMode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AdvancedMode::class)]
#[Group('unit')]
final class AdvancedModeTest extends TestCase
{
    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function storedValues(): array
    {
        return [
            'true'            => [true, true],
            'one'             => [1, true],
            'string one'      => ['1', true],
            'string true'     => ['true', true],
            'padded on'       => [' On ', true],
            'yes'             => ['yes', true],
            'false'           => [false, false],
            'zero'            => [0, false],
            'string zero'     => ['0', false],
            'empty string'    => ['', false],
            'no'              => ['no', false],
            'null'            => [null, false],
            'array'           => [[], false],
            'two isn\'t "on"' => [2, false],
        ];
    }

    #[Test]
    public function isAvailableToAdminsWhateverTheirPreference(): void
    {
        static::assertSame(
            [true, true],
            [
                AdvancedMode::for(
                    admin: true,
                    preference: null,
                )->available,
                AdvancedMode::for(
                    admin: true,
                    preference: true,
                )->available,
            ],
        );
    }

    #[Test]
    public function isOffForEveryoneButAdmins(): void
    {
        $mode = AdvancedMode::for(
            admin: false,
            preference: true,
        );

        static::assertSame([false, false], [$mode->available, $mode->on]);
    }

    #[Test]
    public function isOnForAdminsWhoChoseIt(): void
    {
        static::assertTrue(AdvancedMode::for(
            admin: true,
            preference: '1',
        )->on);
    }

    #[Test]
    #[DataProvider('storedValues')]
    public function readsTheWaysCraftStoresOn(mixed $value, bool $expected): void
    {
        static::assertSame($expected, AdvancedMode::isOn($value));
    }

    #[Test]
    public function savesUnderOnePreferenceSharedByEveryPlugin(): void
    {
        static::assertSame('pkAdvancedMode', AdvancedMode::PREFERENCE);
    }
}
