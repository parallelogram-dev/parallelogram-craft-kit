<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\TestAsset;

use craft\base\Model;

/**
 * A minimal Craft model that methods can be added to in tests. The methods
 * below exist only once a test adds them.
 *
 * @internal
 *
 * @method string describe(string $suffix)
 * @method string first()
 * @method string second()
 * @method mixed unknown()
 */
final class SampleModel extends Model
{
    public string $name = 'sample';
}
