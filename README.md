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
