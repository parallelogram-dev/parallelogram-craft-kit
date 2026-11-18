# Release Notes for Parallelogram Craft Kit

## Unreleased

### Added
- Cache-tag vocabulary (`Tag`), reusing Craft's element tags and adding `p::` tags for non-element content.
- `TaggedCache`, which caches a value and replays the tags it was produced under whenever it is served.
- Craft adapters: `CraftTagCollector`, `YiiTaggedStore`, `CraftInvalidator` and `InvalidationEvents`.
- Site URL providers (`UrlProviderInterface`, `UrlProviderRegistry`, `UrlProviders`).
- `ElementMethods` and `CraftVariables` for adding element methods and `craft.*` variables.
- Test doubles in `parallelogram\craftkit\testing`.
- `stubs/Craft.php` for Mago analysis of Craft plugins.
