# Changelog

## 7.0.0 — Elgg 7.x compatibility

Migration from Elgg 6.x to 7.x.

### Breaking

- **PHP minimum raised to 8.3** (Elgg 7.x requirement).
- **`river_emittable` capability required** — river activity now gated by this capability; registered in `elgg-plugin.php`.

### Migration (6.x → 7.x)

- `elgg-plugin.php`: added `'river_emittable' => true` to `hjplace` entity capabilities.
- Bumped `elgg/elgg: ^7.0`, `php: >=8.3`.

---

## 6.0.0 — Elgg 6.x compatibility

Migration from Elgg 5.x to 6.x.

### Breaking

- **PHP minimum raised to 8.2** (Elgg 6.x requirement).
- **`icontime` metadata removed** — replaced with `$entity->hasIcon()` checks.
  Places plugin entity icon fallback now uses `hasIcon()` instead of checking
  the `icontime` metadata field.

### Changed

- Updated `composer.json` to require `elgg/elgg ^6.0`.
- Replaced `$entity->icontime` checks with `$entity->hasIcon()`.

## 5.0.0 — Elgg 5.x compatibility

Migration from Elgg 4.x to 5.x.

### Breaking

- **`PLUGIN_ID` constant lowercased to `'hypeplaces'`** (was `'hypePlaces'`).
  Any code calling `elgg_get_plugin_setting(..., 'hypePlaces')` must update
  to lowercase.
- **`elgg-plugin.php` `hooks` key renamed to `events`**. Hook handlers now
  receive a single `\Elgg\Event $event` argument (no more 4-param signature).
- **PHP minimum raised to 8.2** (Elgg 5.x requirement).
- **`elgg_trigger_plugin_hook()` replaced by `elgg_trigger_event_results()`**
  in `lib/functions.php::get_icon_sizes()`.

### Changed

- All hook callback functions in `lib/hooks.php` converted to use
  `\Elgg\Event $event` single-argument signature; `$params` → `$event->getParam()`,
  `$return` → `$event->getValue()`.
- Tests updated: `_elgg_services()->session_manager` replaces
  `elgg_get_session()`, `\Elgg\Event` objects used for direct handler tests.
- Docker stack upgraded: PHP 8.2-apache, MySQL 8.0, Playwright 1.59.1.
- Playwright `baseURL` moved into `use:{}` block (required for Playwright ≥1.50).

### Composer

- `php >= 8.2`
- `elgg/elgg ^5.0`

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
