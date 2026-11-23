<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\TestAsset;

use Craft;
use craft\web\Application;
use Yii;

/**
 * Stands a double in for the running Craft application, for code that reads `Craft::$app`, and
 * takes it away again.
 *
 * @internal
 */
final class RunningApp
{
    public static function clear(): void
    {
        Yii::$app = null;
    }

    public static function use(Application $app): void
    {
        Craft::$app = $app;
    }
}
