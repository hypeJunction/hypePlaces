# hypePlaces — Architecture (Elgg 4.x)

## Summary

A directory of businesses, points of interest, and private locations.
Users create `Place` objects with addresses, contact details, and icons.
Places can be bookmarked, checked into, featured by admins, and listed
per-owner, per-group, or sitewide.

## Plugin metadata

- ID / dir name: `hypeplaces` (lowercase, matches `composer.json` name)
- Composer name: `hypejunction/hypeplaces`
- PHP: `>=7.4`
- Elgg: `^4.0`
- Hard dependencies: `hypejunction/hypelists` (declarative; framework lib)
- Soft / optional integrations: hypeMaps, hypeGeo (detected at runtime
  via `is_callable`), hypeFilestore (legacy reference removed in 4.x —
  Elgg core handles entity icons natively)

## Directory layout

```
hypePlaces/
├── elgg-plugin.php             # plugin metadata, entities, actions, routes, hooks, widgets, group_tools
├── composer.json               # 4.x metadata (lowercase name, php 7.4, elgg ^4.0)
├── classes/hypeJunction/Places/
│   ├── Bootstrap.php           # PluginBootstrap — loads lib/ files in load()
│   └── Place.php               # ElggObject subclass, subtype 'hjplace'
├── lib/
│   ├── functions.php           # PLUGIN_ID/PAGEHANDLER constants + helpers
│   ├── hooks.php               # hook callbacks referenced from elgg-plugin.php
│   └── events.php              # (placeholder)
├── actions/places/             # action handlers (4.x response style)
├── views/default/
│   ├── resources/places/       # route resources (replaces 3.x page_handler)
│   ├── framework/places/       # list/profile/edit/sidebar/filter views
│   ├── forms/places/           # form fragments
│   ├── widgets/places*/        # widget content + edit views
│   └── object/hjplace.php      # entity view
├── languages/                  # i18n
├── graphics/                   # default icons
└── tests/                      # PHPUnit + Playwright pre-migration suite
```

## Entities

| Subtype | Class | Capabilities |
|---------|-------|--------------|
| `hjplace` | `\hypeJunction\Places\Place` | commentable, searchable, likable |

`Place` extends `ElggObject` and adds:
- `getAddress()` — structured address tuple
- `getCheckinDuration()` — minutes (configurable plugin setting)
- `getCheckins()` / `isCheckedIn()` / `checkIn()` — annotation-backed visit log

## Routes (replaces 3.x `elgg_register_page_handler('places', ...)`)

| Name | Path | Resource |
|------|------|----------|
| `collection:object:hjplace:all` | `/places` | `places/all` |
| `collection:object:hjplace:featured` | `/places/featured` | `places/all` (filter=featured) |
| `collection:object:hjplace:owner` | `/places/owner/{username}` | `places/owner` |
| `collection:object:hjplace:group` | `/places/group/{guid}` | `places/group` |
| `collection:object:hjplace:bookmarked` | `/places/bookmarked/{username}` | `places/bookmarked` (gatekept) |
| `view:object:hjplace` | `/places/view/{guid}/{title?}` | `places/view` |
| `add:object:hjplace` | `/places/add/{container_guid}` | `places/add` |
| `edit:object:hjplace` | `/places/edit/{guid}` | `places/edit` |

## Actions

| Action | Access |
|--------|--------|
| `places/edit` | logged-in |
| `places/delete` | logged-in (owner-only enforced in handler) |
| `places/feature` | admin |
| `places/unfeature` | admin |
| `places/bookmark` | logged-in |
| `places/unbookmark` | logged-in |
| `places/checkin` | logged-in |
| `places/checkout` | logged-in |

All actions return `elgg_ok_response()` / `elgg_error_response()`
(replaces 3.x `forward()` calls).

## Hooks

| Hook, type | Handler |
|------------|---------|
| `entity:url`, `object` | `\hypeJunction\Places\url_handler` |
| `permissions_check`, `widget_layout` | `\hypeJunction\Places\widget_layout_permissions_check` |
| `register`, `menu:entity` | `entity_menu_setup`, `interactions_menu_setup` |
| `register`, `menu:owner_block` | `owner_block_menu_setup` |
| `register`, `menu:site` | `site_menu_setup` |

Hook callback functions are defined in `lib/hooks.php` and `lib/functions.php`,
loaded by `Bootstrap::load()`.

## Widgets

`places`, `places_about`, `places_activity`, `places_comments` — each
context = profile + dashboard.

## Group tools

| Option | Default |
|--------|---------|
| `places` | on |

(Replaces 3.x `elgg()->group_tools->register('places', ...)` inside
`init()` — now declarative in `elgg-plugin.php`.)

## View extensions

- `elgg.css` ← `css/framework/places/css`
- `groups/tool_latest` ← `framework/places/group_module`

## Migration notes (3.x → 4.x)

What changed:

1. **Removed `start.php`, `activate.php`, `deactivate.php`, `manifest.xml`.**
   All metadata + registrations live in `elgg-plugin.php`.
2. **Bootstrap class.** `\hypeJunction\Places\Bootstrap` loads
   `lib/functions.php`, `lib/hooks.php`, `lib/events.php` in `load()` so
   the constants and hook callbacks referenced from `elgg-plugin.php`
   exist before request handling.
3. **Routes replaced page_handler.** The 3.x `places` page handler with a
   `switch ($context)` block became 8 named routes pointing at
   `views/default/resources/places/*.php` resource scripts.
4. **Actions converted to response objects.** Every `forward(REFERER)` /
   `elgg_register_*_message()` pair became `elgg_ok_response()` /
   `elgg_error_response()`.
5. **`hypeFilestore::IconHandler` removed.** Icon upload now uses
   `$entity->saveIconFromUploadedFile('icon')` (Elgg 4 core API). The
   custom icon-serving page handler and `entity:icon:url` /
   `entity:icon:sizes` hooks were dropped — Elgg core serves entity
   icons natively. **`hypeFilestore` is no longer a runtime dependency**
   and was removed from `composer.json` + `manifest.xml`.
6. **`elgg_register_tag_metadata_name('specialties')` removed.** That
   function does not exist in 4.x; the equivalent in 4.x is the
   `tag_names` config — left as a TODO if `specialties` needs to be
   exposed in tag-style search.
7. **Group tools** moved from imperative `elgg()->group_tools->register()`
   inside `init()` into the declarative `group_tools` key in
   `elgg-plugin.php`.
8. **`composer.json`** rewritten: lowercase `name`, `php >= 7.4`,
   `elgg/elgg ^4.0`, `composer/installers ^2.0`, PSR-4 autoload at
   `classes/hypeJunction/Places/`, `extra.elgg-plugin.id = hypeplaces`.
9. **Lowercase plugin directory.** Mounted into the elgg4 container as
   `hypeplaces` (not `hypePlaces`) per Iron Law 6.

Things deferred / known TODOs (filed as follow-up beads issues if needed):

- `specialties` tag metadata exposure (no equivalent helper called).
- Pre-migration PHPUnit suite needs adapting to 4.x service container
  (`_elgg_services()->session_manager` etc.). The tests still live in
  `tests/phpunit/` from the 3.x baseline.
- `entity_icon_url` / `entity_icon_sizes` hook handlers were removed —
  if any downstream view still references custom icon sizes by name
  ("325x200"), regenerate them via core entity icon API.
- The view files in `views/default/framework/places/*` still reference
  `PAGEHANDLER` constant; they will pick it up from `lib/functions.php`
  (loaded by Bootstrap::load) but should ideally be migrated to
  `elgg_generate_url('collection:object:hjplace:*')` calls.

## Activation gate

Activated successfully against `docker/elgg4` container:

```
$ docker exec -u www-data elgg4-elgg-1 php -r '<bootstrap+activate hypeplaces>'
OK
```

Render gate (homepage / login / `/places`) was not run in a fully booted
fleet because the shared `docker/elgg4` environment has pre-existing
crashes from sibling fleet plugins (`hypePrototyper`, `hypeBlog`) that
crash core boot before any request handler runs. Those failures are
independent of `hypePlaces` (filed separately under MIGRATION-STATUS.md).
