<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft\cp;

use function rtrim;

/**
 * The import map entries that let plugin scripts import the kit and its copy of ParallelogramJS by
 * name, wherever Craft published them:
 *
 *   import { initAll } from '@parallelogram/ui-kit/kit.js';
 *   import { Sortable } from '@parallelogram-js/core/draggable.js';
 *
 * They join Craft's own import map (`View::registerJsImport()`), so the page keeps one map.
 *
 * @api
 */
final class ImportMap
{
    /**
     * The prefix for the kit's own modules.
     */
    public const string KIT = '@parallelogram/ui-kit/';

    /**
     * The prefix for the kit's copy of ParallelogramJS.
     */
    public const string PARALLELOGRAM_JS = '@parallelogram-js/core/';

    /**
     * The entries for a kit published at a base URL, keyed by module specifier prefix.
     *
     * @return array<string, string>
     */
    public static function entries(string $baseUrl): array
    {
        $base = rtrim($baseUrl, characters: '/') . '/';

        return [
            self::KIT              => $base,
            self::PARALLELOGRAM_JS => "{$base}vendor/parallelogram-js/",
        ];
    }
}
