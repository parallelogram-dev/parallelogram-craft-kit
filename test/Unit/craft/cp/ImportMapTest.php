<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft\cp;

use parallelogram\craftkit\craft\cp\ImportMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ImportMap::class)]
#[Group('unit')]
final class ImportMapTest extends TestCase
{
    #[Test]
    public function addsOneSlashWhateverTheBaseUrlEndsWith(): void
    {
        static::assertSame(ImportMap::entries('https://x.test/cp/'), ImportMap::entries('https://x.test/cp'));
    }

    #[Test]
    public function mapsTheKitAndItsCopyOfParallelogramJsToWhereTheyWerePublished(): void
    {
        static::assertSame(
            [
                '@parallelogram/ui-kit/'  => '/cpresources/ab12/',
                '@parallelogram-js/core/' => '/cpresources/ab12/vendor/parallelogram-js/',
            ],
            ImportMap::entries('/cpresources/ab12'),
        );
    }
}
