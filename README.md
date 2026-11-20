# Parallelogram Craft Kit

Shared contracts and Craft CMS adapters used by Parallelogram's Craft plugins (Images, SEO, Nav and Cache). With it, those plugins can work together when they are installed side by side, without any of them requiring another.

It is a Composer library, not a Craft plugin, and is installed automatically by the plugins that need it.

## Requirements

- PHP 8.3 or later
- Craft CMS 5.6 or later

## What's inside

### Cache tags

Craft already tags anything rendered inside a `{% cache %}` block with the elements and element queries it touched, and invalidates those tags when elements are saved. Craft Kit builds on that same mechanism instead of keeping its own records.

- `Tag` builds tags. Element tags use Craft's own format (`element::12`). Content that isn't an element uses the `p::` prefix:

  ```php
  Tag::custom('nav', 'main'); // p::nav:main
  Tag::site(1);               // p::site:1
  Tag::ALL;                   // p::all
  ```

- `TaggedCache::remember()` caches a value and replays its tags every time it is served. If a cached menu is rendered on a page that is itself being cached, the page is still tagged with every entry the menu links to.

  ```php
  $cache = new TaggedCache(
      new YiiTaggedStore(Craft::$app->getCache()),
      new CraftTagCollector(Craft::$app->getElements()),
  );

  $tree = $cache->remember("nav:main:{$siteId}", fn() => $this->resolveTree('main', $siteId));
  ```

- `CraftInvalidator::invalidate()` invalidates `p::` tags and announces them to other plugins.
- `InvalidationEvents::listen()` is a single hook for every invalidation, whether Craft raised it because an element was saved or a Parallelogram plugin raised it. A full-page cache uses this to purge pages.

### Site URLs

A plugin that knows a site's public URLs (such as a sitemap) registers a provider. A plugin that needs them (such as a cache warmer) collects them:

```php
UrlProviders::register(new SitemapUrlProvider());

foreach (UrlProviders::collect()->urlsForSite($siteId) as $siteUrl) {
    // $siteUrl->url, $siteUrl->lastModified
}
```

### Element methods and Twig variables

```php
ElementMethods::add(Asset::class, name: 'picture', method: fn(Asset $asset, string $matrix, array $options = []) => ...);
CraftVariables::add('pNav', NavVariable::class);
```

Templates can then call `asset.picture('hero')` and `craft.pNav`. The method receives the element as its first argument. On Craft 5 this is done with a behavior. The Craft 6 release will use Laravel macros, so plugin code stays the same on both versions.

### Testing helpers

`parallelogram\craftkit\testing` ships with in-memory doubles for plugin test suites: `InMemoryTagCollector`, `InMemoryTaggedStore`, `RecordingInvalidator` and `StaticUrlProvider`. `InMemoryTagCollector` nests and merges collections the same way Craft does.

### CP UI kit

Shared control panel pieces, so Parallelogram plugins look and behave alike, and native. Icons are always Craft's own system icons (`iconSvg()`), never drawings of the kit's own.

Register a plugin once, in its `init()`:

```php
use parallelogram\craftkit\craft\cp\CpUi;

CpUi::register($this); // the `_pk` template root and the `pk-ui` controller
```

Then, in a CP template:

```twig
{% import '_pk/macros' as pk %}
{{ pk.assets() }} {# once per page: scripts, styles, import map entries and icons #}
```

#### Badges, counts, icons and tooltips

```twig
{{ pk.icon('eye') }}
{{ pk.badge('Hidden') }} {{ pk.badge('Expired', 'warning') }} {{ pk.badge('Missing', 'danger') }}
{{ pk.count(8, 2) }}                                     {# 8 items (2 hidden) #}
{{ pk.count(0, 0, { empty: 'No menus yet' }) }}
Drag to reorder {{ pk.info('To put an item inside another, drag it right.') }}
```

#### Simple / Advanced

Admins get a switch; everyone else gets nothing, and always sees the simple view. Anything marked `data-pk-advanced` shows only while the switch is on. The choice is saved to the user's `pkAdvancedMode` preference, shared by every plugin.

```twig
{{ pk.advancedSwitch('p-nav') }}
<div class="field" data-pk-advanced>…handles, paths, raw options…</div>
```

In PHP, `AdvancedMode::for($user->admin, $user->getPreference(AdvancedMode::PREFERENCE))->on` gives the same answer.

#### Repeaters

Rows added with "+ Add …", reordered by their handle, removed with ×, checked as they change. By default they submit as `name[0][field]`; `mode: 'json'` submits one input holding a JSON array.

```twig
{{ pk.repeater('profiles', [
    { name: 'type', label: 'Type', type: 'select', required: true, options: [
        { value: 'instagram', label: 'Instagram' }, { value: 'other', label: 'Other' },
    ] },
    { name: 'url', label: 'Address', type: 'url', required: true },
], values.profiles, { addLabel: 'Add a profile', errors: errors.profiles ?? {} }) }}
```

Field types: `text`, `url`, `email`, `select`, `checkbox`. Fields can be `required` and carry a `pattern` with a `patternMessage`.

#### Searchable selects

ParallelogramJS's `<p-select>`, with groups and descriptions. `custom: true` lets someone keep what they typed when nothing matches.

```twig
{{ pk.select('schemaType', [
    { value: 'Article', label: 'Article', group: 'Creative works', description: 'A news or blog article.' },
    { value: 'ProfessionalService', label: 'Professional service', group: 'Organisations' },
], profile.schemaType, { ariaLabel: 'Schema type', custom: true }) }}
```

#### Modals

ParallelogramJS's `<p-modal>`: the body scrolls and the footer stays put.

```twig
{% embed '_pk/modal' with { id: 'add-file', title: 'Add a root file', primaryLabel: 'Add file' } %}
    {% block body %}…fields…{% endblock %}
{% endembed %}
{{ pk.modalButton('add-file', 'Add a root file') }}
```

#### In script

`UiKitAsset` adds two entries to Craft's own import map (`View::registerJsImport()`, Craft 5.6+), so plugin scripts loaded as modules can import the kit and its copy of ParallelogramJS by name:

```js
import { initAll, createModal, countLabel } from '@parallelogram/ui-kit/kit.js';
import { Sortable } from '@parallelogram-js/core/draggable.js';

createModal({ title: 'Edit item', body: form, primaryLabel: 'Apply', onPrimary: () => apply() });
initAll(container); // after adding kit markup yourself
```

The pinned ParallelogramJS files are committed in `src/craft/cp/dist/vendor`. To update them, change the version in `package.json`, then run `npm ci && npm run vendor`. `npm run test:js` runs the kit's script tests.

### Mago stub

Mago reads `Craft::$app` as possibly null, which floods a plugin's analysis with false errors. `stubs/Craft.php` fixes this. Add it to a plugin's `mago.toml`:

```toml
[source]
includes = ["vendor", "vendor/parallelogram/craft-kit/stubs"]
excludes = ["vendor/craftcms/cms/src/Craft.php"]
```

## Development

```bash
composer install
composer check
```

`composer check` runs Mago (format, lint, analyse) and PHPUnit. Tests live in `test/` and the suite requires 100% line and branch coverage:

```bash
XDEBUG_MODE=coverage vendor/bin/phpunit --path-coverage --coverage-text
```

## Licence

MIT. See [LICENSE.md](LICENSE.md).
