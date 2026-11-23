<?php

/**
 * Analysis-only stub for Mago. Never autoloaded.
 *
 * Craft inherits `$app` from Yii typed as nullable Yii applications, so every
 * `Craft::$app->getX()` reads as a possible null and Craft-only getters look
 * unknown. This redeclares it as the Craft applications it always is at
 * runtime, and declares Craft's own static helpers that plugins call. Plugins
 * include this file in Mago's `source.includes` and exclude
 * `vendor/craftcms/cms/src/Craft.php`.
 */
class Craft extends Yii
{
    /**
     * @var \craft\web\Application|\craft\console\Application
     */
    public static $app;

    /**
     * A Guzzle client with Craft's defaults, as `Craft::createGuzzleClient()` makes.
     *
     * @param array<string, mixed> $config
     */
    public static function createGuzzleClient(array $config = []): \GuzzleHttp\Client
    {
        return new \GuzzleHttp\Client($config);
    }
}
