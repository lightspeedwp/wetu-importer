# `class-lsx-wetu-importer-destination.php`

## Class `LSX_WETU_Importer_Destination`

**Extends:** `LSX_WETU_Importer` (base class)

**Responsibility:** Handles the admin UI, search, and import logic for syncing WETU "Destination"/"Area" pins into the `destination` custom post type. Similar structure to the Accommodation class, but adds destination-specific concerns: parent/child hierarchy (country → destination), continent tagging, and "travel information" fields (electricity, banking, cuisine, climate, transport, dress, health, safety, visa, general info).

### Properties

| Property | Visibility | Type / Default | Description |
|---|---|---|---|
| `$tab_slug` | public | `string` = `'destination'` | Slug identifying this importer tab. |
| `$url` | public | `string\|false` = `false` | Base WETU API endpoint (`https://wetu.com/API/Pins/{api_key}`). |
| `$url_qs` | public | `string\|false` = `false` | Query string (`all=include`) appended to WETU API requests. |
| `$options` | public | `string\|false` = `false` (array once set) | Plugin-wide options from `_lsx-to_settings`, scoped to plugin slug. |
| `$destination_options` | public | `string\|false` = `false` (array once set) | Previously-selected "content to sync" checkboxes, persisted in `lsx_wetu_importer_destination_settings`. |

### Methods

#### `__construct()`
- **Visibility:** public, non-static.
- **Description:** Constructor; calls `set_variables()`.
- **Side effects:** None directly.

#### `set_variables()`
- **Visibility:** public, non-static.
- **Description:** Calls `parent::set_variables()`, then builds the destination WETU API URL/query string and loads cached options/settings.
- **Side effects:** Reads options `_lsx-to_settings` and `lsx_wetu_importer_destination_settings` via `get_option()`.

#### `display_page()`
- **Visibility:** public, non-static.
- **Description:** Renders the Destination import tab admin screen: search form/results table, an import-settings panel with checkboxes for content to sync (title, description, gallery, location, videos), additional content (set country, set continent, featured image, banner image — conditionally shown based on `$this->options` disable flags), a "Travel Information" panel (electricity, banking, cuisine, climate, transport, dress, health, safety, visa, general/additional_info), and (if `LSX_TO_Team` class exists) team-member checkboxes. Shows a "Completed" panel noting to check drafts for auto-created countries.
- **Side effects:** None (pure output). Uses `class_exists( 'LSX_TO_Team' )` to conditionally show team assignment.

#### `process_ajax_search()`
- **Visibility:** public, non-static.
- **Description:** AJAX handler (action `lsx_tour_importer`, type `destination`) mirroring the accommodation version: either filters already-imported destination posts by status/queue membership (via `find_current_accommodation( 'destination' )`), or performs a live WETU search (`/Search/{keywords}/?all=include`), restricting results to `type` of `Destination` or `Area`. Builds and prints HTML rows via `format_row()`.
- **Side effects:** Remote GET to WETU search endpoint. Reads `$_POST['keyword']`/`$_POST['type']`. Outputs via `print_r()`/`esc_attr()` and `die()`. No DB writes.

#### `prepare_row_attributes( $cs_key, $ccs_id )`
- **Visibility:** public, non-static.
- **Params:** `$cs_key`, `$ccs_id` (int post ID).
- **Description:** Builds a normalized row array (id, type=`'Destination'`, name, last_modified, post_id) for an already-imported destination, used by `format_row()`.
- **Side effects:** Calls `get_the_title()`; no writes.

#### `format_row( $row = false, $row_key = '' )`
- **Visibility:** public, non-static.
- **Params:** `$row` (array|false), `$row_key` (default `''`, used numerically).
- **Description:** Converts a single destination row into an HTML `<tr>` string for the admin results table (identical structure/logic to the accommodation class's version).
- **Side effects:** None (pure string building).

#### `process_ajax_import()`
- **Visibility:** public, non-static.
- **Description:** AJAX handler (action `lsx_import_items`, type `destination`) that reads `wetu_id`, `post_id` (also sets `$this->current_post = get_post( $post_id )` when provided), `team_members`, and `content` from `$_POST`; persists selected content settings; fetches the full record from the WETU `/Get` endpoint; delegates to `import_row()`; on success removes from queue and formats a completed row; on failure emits an error message.
- **Hooks:** Fires filter `lsx_wetu_language` on the fetch URL, same as the accommodation class.
- **Side effects:** `delete_option( 'lsx_wetu_importer_destination_settings' )` then `add_option()` with new selection. Remote GET to WETU. Sets `$this->current_post` (instance state) when a post ID is supplied. Calls `import_row()`, `remove_from_queue()`, `format_completed_row()`.

#### `remove_from_queue( $id )`
- **Visibility:** public, non-static.
- **Params:** `$id` (int).
- **Description:** Identical logic to the Accommodation class's method — removes the ID from `$this->queued_imports` and persists it.
- **Side effects:** Writes option `lsx_wetu_importer_que` via `delete_option()` + `update_option()`.

#### `import_row( $data, $wetu_id, $id = 0, $team_members = false, $importable_content = array(), $safari_brands = false )`
- **Visibility:** public, non-static.
- **Params:** Same signature shape as the accommodation class (though `$safari_brands` is unused here, always passed `false` from the caller).
- **Description:** Core destination import routine, only proceeding if the WETU record's `type` is `Destination` or `Area`. If `country` content is selected, calls `check_for_parent()` to determine and set `post_parent` (linking a destination to its country). Builds `post_content` from `general_description` (optionally stripped of tags if `strip_tags` is selected). Inserts or updates the `destination` CPT post (respecting `disable_destination_title` option similarly to accommodation). Then conditionally: finds/attaches media, sets team member, sets map/location data, imports videos, imports each "travel information" field (electricity, cuisine, banking, transport, dress, climate, health, safety, visa, additional_info) via `set_travel_info()`, sets featured/banner image and main gallery (all gated behind attachment discovery via `find_attachments()`), and sets the continent taxonomy via `set_continent()`.
- **Side effects:**
  - **CPT:** `destination` post created/updated, with `post_parent` potentially set to a country destination post ID.
  - **Post meta:** `lsx_wetu_id` (on insert), `lsx_wetu_modified_date` (add/update).
  - Delegates further side effects to `find_attachments()`, `set_team_member()`, `set_map_data()`, `set_video_data()`, `set_travel_info()` (per field), `set_featured_image()`, `set_banner_image()`, `create_main_gallery()`, `set_continent()`, `check_for_parent()`/`set_country()`.
  - No hooks fired directly (aside from those inside called helpers).

#### `set_travel_info( $data, $id, $meta_key, $importable = array( 'none' ) )`
- **Visibility:** public, non-static.
- **Params:** `$data` (array), `$id` (int post ID), `$meta_key` (string, one of the travel-info keys), `$importable` (array, default `array('none')` — the selected content-type list, used to check for `strip_tags`).
- **Description:** Generic setter for a single "travel information" field (electricity, banking, cuisine, etc.); reads `$data[0]['travel_information'][$meta_key]`, optionally strips HTML tags, and saves it as a custom field under the same key name.
- **Side effects:** Post meta keyed by `$meta_key` (e.g. `electricity`, `banking`, `cuisine`, `climate`, `transport`, `dress`, `health`, `safety`, `visa`, `additional_info`) via `save_custom_field()`.

#### `set_continent( $data, $id )`
- **Visibility:** public, non-static.
- **Description:** Determines the destination's continent by resolving the WETU `position.country` to a country code (`to_country_data()`), then a continent code (`to_continent_code()`), then a human-readable continent label (`to_continent_label()`, or `to_continent_region_label()` if the Tour Operator option `enable_search_region_filter` is enabled). Only applies when the destination's `map_object_id` matches its own `position.country_content_entity_id` (i.e., the record itself represents the country-level entity). Creates the `continent` term if it doesn't exist, or assigns the existing term to the post.
- **Side effects:** Taxonomy `continent` — may call `wp_insert_term()` (creating a new term) and/or `wp_set_object_terms()` (assigning it to the post). Depends on the global `tour_operator()->options['display']['enable_search_region_filter']` setting from the Tour Operator plugin. Echoes an error message directly via `echo wp_kses_post( $term->get_error_message() )` if term creation fails (a minor code smell — error is printed inline rather than via `format_error()`).

#### `check_for_parent( $data = array(), $id )`
- **Visibility:** public, non-static.
- **Params:** `$data` (array, default `array()`), `$id` (int, note: no default — PHP will warn/error if omitted, and having a required param after an optional one is also a code smell).
- **Description:** Determines whether the current WETU destination record's country ID differs from its own destination ID (i.e., it's not itself the country-level record); if so, calls the base class's `set_country()` to create/find the parent country `destination` post and returns its post ID (used as `post_parent` in `import_row()`). Returns `0` if the record IS the country itself.
- **Side effects:** Delegates to `$this->set_country()` (base class) which likely creates a placeholder/draft `destination` post for the country if one doesn't already exist — this is the source of the "check the draft list for countries" note shown in `display_page()`.
