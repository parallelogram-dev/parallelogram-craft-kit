<?php

declare(strict_types=1);

namespace parallelogram\craftkit\test\Unit\craft\cp;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\ArrayLoader;
use Twig\TwigFunction;

use function dirname;
use function file_get_contents;
use function is_array;
use function json_decode;
use function preg_match;

/**
 * `pk.tabs()` hands Craft's own `_includes/tabs` template the tabs, the
 * selected one and the container it needs. A stand-in for that template
 * reports what it was given.
 *
 * @internal
 */
#[CoversNothing]
#[Group('unit')]
final class TabsMacroTest extends TestCase
{
    private const string INCLUDE = '<pk-include>{{ { tabs: tabs, selectedTab: selectedTab, tablistLabel: tablistLabel, containerAttributes: containerAttributes }|json_encode|raw }}</pk-include>';

    /**
     * @return array<array-key, mixed>
     */
    private static function asArray(mixed $decoded): array
    {
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @throws Error
     */
    #[Test]
    public function linksTabsInThePaneHeaderForCraftsOwnTabScript(): void
    {
        $html = $this->render(
            "{{ pk.tabs({ main: { label: 'Main Menu', url: '/admin/p-nav/editor/main' }, project: { label: 'Project', url: '/admin/p-nav/editor/project' } }, 'project', { label: 'Menus' }) }}",
        );

        static::assertSame(
            [
                'header' => true,
                'given'  => [
                    'tabs'                => [
                        'main'    => ['label' => 'Main Menu', 'url' => '/admin/p-nav/editor/main'],
                        'project' => ['label' => 'Project', 'url' => '/admin/p-nav/editor/project'],
                    ],
                    'selectedTab'         => 'project',
                    'tablistLabel'        => 'Menus',
                    'containerAttributes' => ['id' => 'tabs'],
                ],
            ],
            [
                'header' => 1 === preg_match('/^<header id="content-header" class="pane-header"><pk-include>/', $html),
                'given'  => $this->given($html),
            ],
        );
    }

    /**
     * @throws Error
     */
    #[Test]
    public function pointsInPageTabsAtTheirPanelsAndLeavesThemToTheKitOutsideThePane(): void
    {
        $html = $this->render(
            "{{ pk.tabs({ general: { label: 'General' }, social: { label: 'Social profiles' } }, null, { pane: false }) }}",
        );

        static::assertSame(
            [
                'header' => false,
                'given'  => [
                    'tabs'                => [
                        'general' => ['label' => 'General', 'url' => '#general'],
                        'social'  => ['label' => 'Social profiles', 'url' => '#social'],
                    ],
                    'selectedTab'         => 'general',
                    'tablistLabel'        => null,
                    'containerAttributes' => ['data' => ['pk-tabs' => true]],
                ],
            ],
            ['header' => 1 === preg_match('/<header/', $html), 'given' => $this->given($html)],
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    private function given(string $html): array
    {
        $match = [];
        preg_match('#<pk-include>(.*)</pk-include>#s', $html, $match);

        return self::asArray(json_decode($match[1] ?? '{}', associative: true));
    }

    /**
     * @throws Error
     */
    private function render(string $call): string
    {
        $twig = new Environment(new ArrayLoader([
            '_pk/macros'     => (string) file_get_contents(
                dirname(__DIR__, levels: 4) . '/src/craft/cp/templates/macros.twig',
            ),
            '_includes/tabs' => self::INCLUDE,
            'page'           => "{% import '_pk/macros' as pk %}{$call}",
        ]));
        $twig->addFunction(new TwigFunction('iconSvg', static fn(): string => ''));

        return $twig->render('page');
    }
}
