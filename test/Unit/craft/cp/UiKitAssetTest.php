<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft\cp;

use craft\web\assets\cp\CpAsset;
use craft\web\View;
use parallelogram\craftkit\craft\cp\ImportMap;
use parallelogram\craftkit\craft\cp\UiKitAsset;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use yii\web\AssetManager;
use yii\web\View as YiiView;

use function is_file;

/**
 * @internal
 */
#[CoversClass(UiKitAsset::class)]
#[UsesClass(ImportMap::class)]
#[Group('unit')]
final class UiKitAssetTest extends TestCase
{
    #[Test]
    public function addsItselfToCraftsImportMapBeforeItsFiles(): void
    {
        $bundle          = new UiKitAsset();
        $bundle->baseUrl = '/cpresources/ab12';
        $view            = $this->createMock(View::class);
        $view->method('getAssetManager')->willReturn($this->assets());
        $view->expects(static::exactly(2))
            ->method('registerJsImport')
            ->willReturnCallback(static function (string $specifier, string $url): void {
                static::assertSame(ImportMap::entries('/cpresources/ab12')[$specifier] ?? null, $url);
            });
        $view->expects(static::once())
            ->method('registerJsFile')
            ->with('/cpresources/ab12/kit.js', ['type' => 'module']);

        $bundle->registerAssetFiles($view);
    }

    #[Test]
    public function addsNothingToTheImportMapBeforeItIsPublished(): void
    {
        $bundle = new UiKitAsset();
        $view   = $this->createMock(View::class);
        $view->method('getAssetManager')->willReturn($this->assets());
        $view->expects(static::never())->method('registerJsImport');

        $bundle->registerAssetFiles($view);
    }

    #[Test]
    public function hasEveryFileItNames(): void
    {
        $source = (new UiKitAsset())->sourcePath ?? '';

        static::assertSame(
            [true, true, true],
            [
                is_file("{$source}/kit.js"),
                is_file("{$source}/kit.css"),
                is_file("{$source}/vendor/parallelogram-js/styles/components/draggable.css"),
            ],
        );
    }

    #[Test]
    public function hasTheCopyOfParallelogramJsTheKitImports(): void
    {
        $vendor = ((new UiKitAsset())->sourcePath ?? '') . '/vendor/parallelogram-js';

        static::assertSame(
            [true, true, true, true],
            [
                is_file("{$vendor}/draggable.js"),
                is_file("{$vendor}/components/PModal.js"),
                is_file("{$vendor}/components/PSelect.js"),
                is_file("{$vendor}/LICENSE"),
            ],
        );
    }

    #[Test]
    public function shipsItsScriptAsAModuleOnTopOfTheControlPanel(): void
    {
        $bundle = new UiKitAsset();

        static::assertSame(
            [[CpAsset::class], ['kit.js'], ['type' => 'module']],
            [$bundle->depends, $bundle->js, $bundle->jsOptions],
        );
    }

    #[Test]
    public function stillRegistersItsFilesWithAPlainYiiView(): void
    {
        $bundle          = new UiKitAsset();
        $bundle->baseUrl = '/cpresources/ab12';
        $view            = $this->createMock(YiiView::class);
        $view->method('getAssetManager')->willReturn($this->assets());
        $view->expects(static::exactly(2))->method('registerCssFile');

        $bundle->registerAssetFiles($view);
    }

    private function assets(): AssetManager
    {
        $assets = $this->createStub(AssetManager::class);
        $assets->method('getAssetUrl')
            ->willReturnCallback(
                static fn(UiKitAsset $bundle, string $file): string => "{$bundle->baseUrl}/{$file}",
            );

        return $assets;
    }
}
