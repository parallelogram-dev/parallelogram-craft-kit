<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft\cp;

use craft\web\assets\cp\CpAsset;
use craft\web\View;
use Override;
use yii\web\AssetBundle;

/**
 * The CP UI kit's scripts and styles, and its copy of ParallelogramJS (the drag engine, PModal and
 * PSelect), made by `npm run vendor` and committed.
 *
 * Registering it also adds the kit to Craft's import map, so plugin scripts can import
 * `@parallelogram/ui-kit/kit.js` and `@parallelogram-js/core/draggable.js` from wherever Craft
 * published the files. It extends Yii's bundle rather than Craft's, since it never uses Craft's
 * Vite dev server.
 *
 * @api
 */
final class UiKitAsset extends AssetBundle
{
    #[Override]
    public function init(): void
    {
        $this->sourcePath = __DIR__ . '/dist';
        $this->depends    = [CpAsset::class];
        $this->js         = ['kit.js'];
        $this->jsOptions  = ['type' => 'module'];
        $this->css        = ['vendor/parallelogram-js/styles/components/draggable.css', 'kit.css'];

        parent::init();
    }

    /**
     * @param \yii\web\View $view
     */
    #[Override]
    public function registerAssetFiles($view): void
    {
        if ($view instanceof View && null !== $this->baseUrl) {
            foreach (ImportMap::entries($this->baseUrl) as $specifier => $url) {
                $view->registerJsImport($specifier, $url);
            }
        }

        parent::registerAssetFiles($view);
    }
}
