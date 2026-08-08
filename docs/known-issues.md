# Known Issues & Code Smells

Discovered while documenting the plugin's classes/functions. These are observations for maintainers, not a fixed backlog — verify before relying on any of them, since behavior may have shifted since this guide was written.

## Bugs

- **`LSX_WETU_Importer::set_taxonomy()`** ([class-lsx-wetu-importer.md](class-lsx-wetu-importer.md)) references undefined variables `$data`/`$tax` instead of its own `$terms`/`$taxonomy`/`$id` parameters — the core loop is dead code as written.
- **`LSX_WETU_Importer_Accommodation::set_facilities()`** ([class-accommodation.md](class-accommodation.md)) doesn't guard the inner facility loop with `isset()`, and doesn't reset `$parent_id` per outer-loop iteration if a facility group is missing from the Wetu payload — a stale parent term ID from a previous group could leak into the next group's term assignment.
- **`LSX_WETU_Importer_Destination::check_for_parent( $data = array(), $id )`** ([class-destination.md](class-destination.md)) has a required parameter (`$id`) after an optional one (`$data`) — valid but unusual PHP, and easy to call incorrectly.
- **`bin/class-wetu-automation.php` → `run_main_actions()`** has the `isset( $wetu_tours[ $wetu_id ] )` guard commented out relative to the `classes/` version, which can cause a null-index warning/notice when a local tour's Wetu ID isn't present in the fetched tour list.
- **`bin/class-wetu-automation.php` → `tour_sync_action()`** references an undefined variable `$flags` in `apply_filters( 'wetu_automation_tour_flags', $flags )` (should be `$importable`, as correctly written in the `classes/` version), and makes a redundant unused `wp_remote_get()` call before the real fetch.
- **`classes/class-wetu-automation.php` → `pins_sync_action()`** calls the Wetu Pins API using a hardcoded API key literal (`ROARLEMOUP5IENOE`) baked into the URL instead of `get_api_key()` — likely a leftover placeholder/debug value that should use the configured key.

## Security

- **`bin/class-wetu-automation.php` → `tester_init()`** wires up unauthenticated `$_GET`-triggered debug entry points (`wetu_main_debug`, `wetu_tour_debug`, `wetu_pin_debug`) on the `init` hook, each of which can trigger a full sync/import operation (external API calls + DB writes) from any front-end/admin page load, with no capability or nonce check. This file is not currently `require`d by the plugin bootstrap, but should either be deleted or hardened (e.g. gated behind `WP_DEBUG` + a capability check) if it is ever restored — see [class-wetu-automation.md](class-wetu-automation.md).
- **`LSX_WETU_Importer::attach_external_image2()`** and its near-duplicate in `LSX_WETU_Importer_Banner_Integration` use the hardcoded `/tmp` directory (`tempnam( '/tmp', 'FOO' )`) rather than WordPress's `get_temp_dir()`, and don't validate that `preg_match()` found a file extension before using `$matches` — a malformed Wetu URL could trigger a PHP warning/fatal rather than failing gracefully.

## Dead code / no-ops

- **`LSX_WETU_Importer::queue_item()`** has a redundant duplicate-check: both branches of its `if/else` append the same value to `$this->import_queue`, so the `in_array()` check has no effect (deduplication only actually happens later in `save_queue()` via `array_unique()`).
- **`LSX_WETU_Importer_Settings::display_page()`** has a `foreach ( $options as $key => $value ) { $value = trim( $value ); }` loop that never writes `$value` back into `$options` — the trim has no effect.
- **`LSX_WETU_Importer_Post_Columns::register_sortable_columns()`** is not wired to any hook in `__construct()` (unlike `register_tour_columns()`/`output_tour_ref_column()`/`tour_search_by_wetu_ref()`), so it appears to be dead/unused code unless attached elsewhere.
- **`LSX_WETU_Importer_Tours::get_mobile_accommodation()`** appears to be an unused/alternative helper — `process_itineraries()` derives accommodation earlier in its loop and calls `get_mobile_destination()` directly rather than calling this method.

## PHP 8.2+ compatibility

- **Dynamic properties**: `LSX_WETU_Importer::set_destination()` / `set_country()` read/write `$this->current_destinations` and `$this->destination_images`, and `bin/class-wetu-automation.php::init()` sets `$this->options`/`$this->api_key` — none of these are declared in their class's property block. This triggers `Deprecated: Creation of dynamic property` warnings on PHP 8.2+ unless the classes are updated to either declare the properties or implement `#[\AllowDynamicProperties]`.

## Minor inconsistencies

- `LSX_WETU_Importer::$import_scaling_url` is declared as a property but the actual scaling URL is assigned to a dynamically-created `$image_scaling_url` property instead — the declared property is effectively unused.
- Several helper functions in `includes/helpers.php` (`lsx_wetu_get_post_count()`, `lsx_wetu_get_queue_count()`, `lsx_wetu_get_tour_count()`) have `@return void` in their docblocks despite actually returning a count value — the docblocks should be corrected to `@return int|string`.
- `LSX_WETU_Importer_Destination::set_continent()` echoes a `WP_Error` message directly (`echo wp_kses_post( $term->get_error_message() )`) rather than routing it through the shared `format_error()` helper used elsewhere for consistent error-row rendering.
- Several `$_GET`/`$_POST` reads in `update_options()` and `set_variables()` lack nonce verification but are marked with `// phpcs:ignore` comments, since they are read-only/non-state-changing usages. Worth re-confirming this is still true if those methods are modified.
