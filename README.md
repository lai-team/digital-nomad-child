# digital-nomad-child

The active theme for beauVoyage member subsites (e.g. `symphony.beau.voyage`).
A child of **Digital Nomad 1.2.0** by [Jasom Dotnet](https://www.jasom.net),
overriding the journey/story layout, the map header, and the sidebar.

> **This theme is used for all subsites, so anything it needs must be defined
> programmatically.** There is no per-site manual configuration step — a new
> subsite gets this theme switched on by `bv_create_subsite()` in the `voyage`
> plugin and must work immediately.

## Requirements

- WordPress **multisite**
- Parent theme **`digital-nomad`** (1.2.0) installed alongside this one
- PHP 7.4+
- Plugins, all of which this theme calls into directly:
  - **`voyage`** — `bv_get_adjac_post()`, `bv_get_adjacent_post()`, `logErrors()`,
    and the global `$` (see below)
  - **`bv_map`** — `bv_get_geompoint()`, the `bv-map-js` script handle, and the
    `map` / `bvVarMapGeoJson` browser globals
  - **The Events Calendar** — itineraries are `EVENTS_POSTTYPE`
  - **Advanced Custom Fields Pro**, **WP-GeoMeta** — geo metadata

## Configuration

Constants read from `wp-config.php`, none of them guarded with `defined()`:

| Constant | Used for |
| --- | --- |
| `MAPBOX_TOKEN` | Static map header images |
| `MAPBOX_STYLE_SATELLITE` | Style for those images |
| `EVENTS_POSTTYPE` | The Events Calendar post type |
| `FORECAST_DAYS_AHEAD_BASIC` / `_PREMIUM` | Weather forecast window per plan |

## The cross-plugin contract

This theme and the two `bv_*` plugins are coupled in both directions. Breaking
either side has taken the site down before, so it is written out here.

**What this theme provides to the plugins**

| Provided | Consumed by |
| --- | --- |
| `pv.template_path` (localised onto `child-script`) | `bv_map/assets/js/main.js` |
| `initCoords` (localised in `category.php` / `index.php`) | `assets/js/list-stories.js` |
| `load_more_top` / `load_more_bottom` globals | `bv_map` resets them on marker click |
| `inc/ajax.php` — the `jumpto_post` endpoint | `bv_map`, to load a story not yet on the page |

**What this theme consumes from the plugins**

| Consumed | Provided by |
| --- | --- |
| `bv_get_geompoint()` | `bv_map` |
| `bv_get_adjac_post()`, `bv_get_adjacent_post()`, `logErrors()` | `voyage` |
| `bv-map-js` script handle (a declared dependency of `bv-list-stories-script`) | `bv_map` |
| `map`, `bvVarMapGeoJson` browser globals | `bv_map/assets/js/main.js` |
| **the global `$`** | `voyage/assets/js/front-end.js` |

### The global `$`

`assets/js/main.js:101` and `assets/js/list-stories.js:15` both call `$()` at
top level. WordPress runs jQuery in noConflict mode, so `window.$` is **not**
defined by core — it is defined by `voyage/assets/js/front-end.js`, which must
therefore load in the head, ahead of `child-script`. Removing that line from
`voyage` threw `TypeError: $ is not a function` on every page of this theme.

The durable fix is for this theme to stop depending on a global and use
`jQuery( function ( $ ) { … } )` instead. Until then, the dependency is real
and undeclared.

### `bv-map-js` as a hard dependency

`functions.php:14` enqueues `bv-list-stories-script` with `bv-map-js` in its
dependency array. If `bv_map` is deactivated, WordPress cannot resolve it and
`list-stories.js` silently never loads. This also constrains when `bv_map` may
register that handle — registering it too early changes where both scripts
print.

## Layout

```
functions.php                     Enqueues, pv localisation, adjacent-post
                                  preview rendering, forecast helpers
index.php                         Blog index; localises initCoords
category.php                      Journey archive; localises initCoords
single.php                        Single story; Mapbox static image header
header.php                        Header override
inc/ajax.php                      Raw POST endpoint (jumpto_post)
templates/sidebar-*.php           Sidebar, modal menu and search partials
templates/header-logo.php         Logo partial
templates/category_list_widget.php
templates/category_exclude_settings.json  Categories hidden from listings
assets/js/main.js                 Theme behaviour
assets/js/list-stories.js         Infinite scroll; drives map.flyTo/fitBounds
assets/js/cc_weather.js           Forecast widget
assets/css/sidebar-modal-menu.css
assets/fonts/google-font-settings.json    Google Fonts to enqueue
style.css                         Child theme styles and theme header
```

## Known issues

- **`inc/ajax.php` is a raw POST endpoint.** It bootstraps WordPress by
  path-munging `__DIR__` to find `wp-load.php`, then reads `$_POST['action']`
  and `$_POST['data']` at file scope with **no nonce and no capability check**,
  rather than going through `admin-ajax.php` or the REST API. It is directly
  addressable at `<theme-uri>/inc/ajax.php`. Same shape as the endpoints that
  were removed from `bv_map`; this one has not been migrated.
- **Constants are unguarded.** `MAPBOX_TOKEN`, `EVENTS_POSTTYPE` and the
  forecast constants are used bare, so an environment without this exact
  `wp-config.php` fatals on PHP 8 rather than degrading.
- `functions.php` contains a commented-out second definition of
  `digitalnomad_get_the_date_ago()`; the live one is at line 96.
- Indentation is mixed — PHP and CSS use tabs, the JS under `assets/js/` uses
  four spaces. Nothing has been reformatted.

A hardcoded Mapbox token belonging to a third-party account (`gentritbiba`, the
original developer) was removed from `single.php` before the first commit, so
it does not appear in this repository's history. It sat inside a CSS comment,
but the PHP within still executed, so it was being emitted into the page source
of every single-post view. It was a public `pk.` token rather than a secret,
and the site's own key is the `MAPBOX_TOKEN` constant — but it was not ours to
publish.

## License

GPL-2.0-or-later, as declared in the theme header inherited from the parent.
See [LICENSE](LICENSE).

## Credits

Parent theme **Digital Nomad** by Jasom Dotnet. Child theme by the Lai
Consulting Team. Previously hosted at `gitlab.com/beauvoyage/digital-nomad-child`.
