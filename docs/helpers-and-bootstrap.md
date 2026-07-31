# Helpers & Bootstrap

## `includes/helpers.php`

Plain procedural helper functions (no classes), guarded by `if ( ! defined( 'ABSPATH' ) ) { exit; }`.

### `lsx_wetu_get_options()`
`function lsx_wetu_get_options()` → `array`
Retrieves the plugin's settings from the `lsx_wetu_importer_settings` option. If that option is empty, falls back to reading a legacy option `_lsx-to_settings` (checking `['lsx-wetu-importer']` and `['api']['wetu_api_key']` sub-keys) for backward compatibility with an older settings structure, merging the legacy API key into the returned array under `api_key`.
- **Side effects:** Reads two WordPress options (`lsx_wetu_importer_settings`, `_lsx-to_settings`); no writes.

### `lsx_wetu_get_post_count( $post_type = '', $post_status = '' )`
`function lsx_wetu_get_post_count( $post_type = '', $post_status = '' )`
Returns the count of posts of the given `$post_type`/`$post_status` combination. Special-cased for `'tour'`: rather than trusting the raw count, it cross-references the `lsx_ti_tours` transient (cached list of Wetu tours) against each matching post's `lsx_wetu_id` meta (lower-cased) to count only tours that still exist in the current Wetu tour list; if the transient is unavailable, returns `0` for tours regardless of the raw DB count.
- **Side effects:** Direct uncached `$wpdb` queries (`SELECT COUNT`, and for tours, a second `SELECT ID` query); reads the `lsx_ti_tours` transient; reads `lsx_wetu_id` post meta per matched tour post (N+1 style loop).

### `lsx_wetu_get_queue_count( $post_type = '' )`
`function lsx_wetu_get_queue_count( $post_type = '' )`
Reads the `lsx_wetu_importer_que` option (array keyed by post type) and returns the count of queued items for the given `$post_type`, or `'0'` if none.
- **Side effects:** Reads the `lsx_wetu_importer_que` option.

### `lsx_wetu_get_tour_count( $post_type = '' )`
`function lsx_wetu_get_tour_count( $post_type = '' )`
Reads the `lsx_ti_tours` transient (defaulting to `array()`) and returns its count, i.e. the number of tours currently cached from the Wetu API. The `$post_type` parameter is accepted but never used inside the function body.
- **Side effects:** Reads the `lsx_ti_tours` transient only.

---

## `lsx-importer-for-wetu.php` (main plugin bootstrap file)

Not a class — plain WordPress plugin header + minimal bootstrap logic.

**Plugin metadata (from header):** Name "Wetu Content Importer", Version `1.5.2`, requires WP 6.7+/PHP 8.0+, requires the `tour-operator` plugin, text domain `lsx-wetu-importer`.

### What it does

- Exits immediately if `ABSPATH` isn't defined (direct-access guard).
- Defines four constants:
  - `LSX_WETU_IMPORTER_PATH` = `plugin_dir_path( __FILE__ )` — absolute filesystem path to the plugin root.
  - `LSX_WETU_IMPORTER_CORE` = `__FILE__` — path to this bootstrap file itself (used as the activation-hook file reference).
  - `LSX_WETU_IMPORTER_URL` = `plugin_dir_url( __FILE__ )` — public URL to the plugin root.
  - `LSX_WETU_IMPORTER_VER` = `'1.5.2'` — plugin version string, matching the header.
- Registers the plugin's activation hook: `register_activation_hook( LSX_WETU_IMPORTER_CORE, array( 'LSX_WETU_Importer', 'register_activation_hook' ) )` — delegates activation logic to the static `register_activation_hook()` method on `LSX_WETU_Importer`.
- Requires `classes/class-lsx-wetu-importer.php`, which bootstraps the entire class hierarchy (see [architecture.md](architecture.md)).

No other class/function definitions live in this file.
