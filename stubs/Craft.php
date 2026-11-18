<?php

/**
 * Analysis-only stub for Mago. Never autoloaded.
 *
 * Craft inherits `$app` from Yii typed as nullable Yii applications, so every
 * `Craft::$app->getX()` reads as a possible null and Craft-only getters look
 * unknown. This redeclares it as the Craft applications it always is at
 * runtime. Plugins include this file in Mago's `source.includes` and exclude
 * `vendor/craftcms/cms/src/Craft.php`.
 */
class Craft extends Yii
{
    /**
     * @var \craft\web\Application|\craft\console\Application
     */
    public static $app;
}
