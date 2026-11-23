# Release Notes for Parallelogram Craft Kit

## 0.2.1 - 2026-11-23

### Fixed
- The Mago stub for `Craft` declares `createGuzzleClient()`, so plugins that call it analyse cleanly when craft-kit is installed from Packagist.

## 0.2.0 - 2026-11-23

### Added
- `pk.chips(name, values, options)`: short values such as query parameter names, added with Enter or a comma and removed with ×; posts `name[]`.
- `pk.advancedSwitch(handle, label, { header: true })` puts the switch beside the page's Save button; `[data-pk-simple]` marks parts shown only while Advanced is off.
- A control panel UI kit for Parallelogram plugins (`parallelogram\craftkit\craft\cp`):
  - `CpUi::register($plugin)` adds the `_pk` template root and a `pk-ui` controller to a plugin.
  - `UiKitAsset` ships the kit's scripts and styles and a pinned copy of ParallelogramJS 0.7.29 (the drag engine, PModal and PSelect), and adds entries to Craft's import map so plugin scripts can import `@parallelogram/ui-kit/kit.js` and `@parallelogram-js/core/…`.
  - Twig macros (`{% import '_pk/macros' as pk %}`): `assets`, `icon`, `info`, `badge`, `count`, `advancedSwitch`, `repeater`, `select`, `tabs` and `modalButton`, plus `{% embed '_pk/modal' %}`.
  - `pk.tabs()` outputs Craft's own card tabs (`_includes/tabs`), either as the content pane's header, which Craft's tab script runs (or the kit, where a namespace such as a plugin's settings page renames `#tabs`), or anywhere else, where the kit shows the selected in-page tab's panel.
  - Scripts: repeaters, searchable selects with custom values, modals with a fixed footer, ⓘ tooltips, and the admin-only Simple / Advanced switch, saved to the user's preferences (`pkAdvancedMode`).
  - Icons are always Craft's own system icons.

## 0.1.0 - 2026-11-18

### Added
- Cache-tag vocabulary (`Tag`), reusing Craft's element tags and adding `p::` tags for non-element content.
- `TaggedCache`, which caches a value and replays the tags it was produced under whenever it is served.
- Craft adapters: `CraftTagCollector`, `YiiTaggedStore`, `CraftInvalidator` and `InvalidationEvents`.
- Site URL providers (`UrlProviderInterface`, `UrlProviderRegistry`, `UrlProviders`).
- `ElementMethods` and `CraftVariables` for adding element methods and `craft.*` variables.
- Test doubles in `parallelogram\craftkit\testing`.
- `stubs/Craft.php` for Mago analysis of Craft plugins.
