# Changelog

## 4.0.0 — Elgg 4.x compatibility

Migration from Elgg 3.x to 4.x.

### Breaking

- **Plugin directory name lowercased to `hypeplaces`** (Iron Law 6 — must
  match `composer.json` `name` field). Update any deployment scripts
  that reference `mod/hypePlaces`.
- **`hypeFilestore` is no longer required.** Icon handling now uses
  Elgg core (`$entity->saveIconFromUploadedFile()` and core icon URLs).
  Custom icon sizes (`125`, `325x200`) and the icon-serving page handler
  were removed.
- **Page handler removed.** All `places/*` URLs are now driven by named
  routes (`collection:object:hjplace:*`, `view:object:hjplace`, etc.).
  External links to legacy paths still work because the routes match
  the same paths, but anything URL-building should switch to
  `elgg_generate_url()`.
- **Action responses.** All actions now return `elgg_ok_response()` or
  `elgg_error_response()`; AJAX consumers receive a structured response
  instead of an HTTP redirect.

### Removed

- `start.php`, `activate.php`, `deactivate.php`, `manifest.xml`
- `lib/page_handlers.php`
- `entity:icon:url` / `entity:icon:sizes` hooks (icon handling moved to
  Elgg core)
- `elgg_register_tag_metadata_name('specialties')` — function removed in
  Elgg 4.x; use the `tag_names` config if needed

### Added

- `elgg-plugin.php` declarative manifest with metadata, entities,
  actions, routes, hooks, widgets, view extensions, group tools
- `classes/hypeJunction/Places/Bootstrap.php` (`PluginBootstrap`
  subclass)
- `views/default/resources/places/*.php` route resources for the 8
  routes
- `ARCHITECTURE.md` describing the 4.x layout and migration notes

### Composer

- `php >= 7.4`
- `elgg/elgg ^4.0`
- `composer/installers ^2.0`
- PSR-4 autoload `hypeJunction\Places\` ⇒ `classes/hypeJunction/Places/`
- `name` lowercased to `hypejunction/hypeplaces`
- `extra.elgg-plugin.id = hypeplaces`
