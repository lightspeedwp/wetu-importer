# `class-lsx-wetu-importer-accommodation.php`

## Class `LSX_WETU_Importer_Accommodation`

**Extends:** `LSX_WETU_Importer` (base class defined in `class-lsx-wetu-importer.php`)

**Responsibility:** Handles the admin UI, search, and import logic for syncing WETU "Accommodation" pins into the `accommodation` custom post type provided by the Tour Operator plugin. It renders the admin tab (`display_page`), handles two AJAX endpoints (search WETU/local content, and import a single item), and maps the WETU JSON payload into WordPress post data, meta fields, and taxonomies (rooms, rating, facilities, brand, travel style, etc.).

### Properties

| Property | Visibility | Type / Default | Description |
|---|---|---|---|
| `$tab_slug` | public | `string` = `'accommodation'` | Slug identifying this importer tab; used in admin URLs and AJAX routing. |
| `$url` | public | `string\|false` = `false` | Base WETU API endpoint (`https://wetu.com/API/Pins/{api_key}`), set in `set_variables()`. |
| `$url_qs` | public | `string\|false` = `false` | Query string appended to WETU API requests (`all=include`). |
| `$options` | public | `string\|false` = `false` (array once set) | Plugin-wide options pulled from the `_lsx-to_settings` option, scoped to this plugin's slug. |
| `$accommodation_options` | public | `string\|false` = `false` (array once set) | Previously-selected "content to sync" checkboxes, persisted in the `lsx_wetu_importer_accommodation_settings` option, used to pre-check the import form. |

### Methods

#### `__construct()`
- **Visibility:** public, non-static.
- **Description:** Constructor; simply calls `set_variables()` to initialize the API URL and cached options when the class is instantiated.
- **Side effects:** None directly (delegates to `set_variables()`).

#### `set_variables()`
- **Visibility:** public, non-static.
- **Description:** Calls `parent::set_variables()` (to set shared properties like `$api_key`, `$plugin_slug`, `$queued_imports`, etc. from the base class), then builds the accommodation-specific WETU API URL and loads cached settings.
- **Side effects:** Reads options `_lsx-to_settings` and `lsx_wetu_importer_accommodation_settings` via `get_option()`. No writes.

#### `display_page()`
- **Visibility:** public, non-static.
- **Description:** Renders the full admin screen markup for the Accommodation import tab: search form, results table, an "import list" form with checkboxes for which content types to sync (title, description, excerpt, gallery, category, location, destination, rating, rooms, checkin/checkout, facilities, friendly, special interests, spoken languages, videos, featured/banner image), team-member checkboxes, and safari-brand taxonomy checkboxes. Also shows a "Completed" panel linking to the Destination import tab.
- **Side effects:** None (pure output/echo). Calls helper methods from the base class: `search_form()`, `table_header()`, `table_footer()`, `team_member_checkboxes()`, `taxonomy_checkboxes()`.

#### `process_ajax_search()`
- **Visibility:** public, non-static.
- **Description:** AJAX handler (action `lsx_tour_importer`, type `accommodation`) that either (a) filters previously-imported/queued accommodation posts by status (`publish`, `pending`, `draft`, `import`) using locally stored post data, or (b) if no status keyword is recognized, performs a live keyword search against the WETU `/Search/{keywords}` endpoint, filtering out non-accommodation types (Destination, Activity, Restaurant, None, Site/Attraction). Builds HTML table rows via `format_row()` and prints them for the admin JS to inject.
- **Hooks:** None registered/fired directly, but relies on nonce action `lsx_wetu_ajax_action` via `check_ajax_referer()`.
- **Side effects:** Remote GET to WETU Search API (`wp_remote_get`). Reads `$_POST['keyword']`, `$_POST['type']`. Outputs via `print_r()` and terminates with `die()`. No DB writes.

#### `prepare_row_attributes( $cs_key, $ccs_id )`
- **Visibility:** public, non-static.
- **Params:** `$cs_key` (mixed, WETU ID/key), `$ccs_id` (int, WP post ID).
- **Description:** Builds a normalized row array (id, type, name, last_modified, post_id) representing an already-imported accommodation post, used as input to `format_row()`.
- **Side effects:** Calls `get_the_title()`; no writes.

#### `format_row( $row = false, $row_key = 0 )`
- **Visibility:** public, non-static.
- **Params:** `$row` (array|false), `$row_key` (int, default `0`).
- **Description:** Converts a single accommodation row/result into an HTML `<tr>` string for display in the admin results table, including a status link (edit link + post status) or "import" label if not yet imported.
- **Side effects:** None (pure string building); calls `get_post_status()`, `admin_url()`.

#### `remove_from_queue( $id )`
- **Visibility:** public, non-static.
- **Params:** `$id` (int, post ID to remove from the import queue).
- **Description:** Removes a given post ID from the in-memory `$this->queued_imports` array (inherited from base class) and persists the updated queue.
- **Side effects:** Writes to the `lsx_wetu_importer_que` option via `delete_option()` + `update_option()`.

#### `process_ajax_import()`
- **Visibility:** public, non-static.
- **Description:** AJAX handler (action `lsx_import_items`, type `accommodation`) that reads `wetu_id`, `post_id`, `team_members`, `safari_brands`, and `content` (checked content-type checkboxes) from `$_POST`, persists the selected content settings, fetches the full WETU record via the `/Get` endpoint, and delegates to `import_row()` to perform the actual import. On success it formats a "completed" row, removes the item from the queue, and runs post cleanup; on failure, it emits an error message.
- **Hooks:** Fires the filter `lsx_wetu_language` on the request URL (`apply_filters( 'lsx_wetu_language', ... )`) allowing other code to localize/modify the WETU fetch URL.
- **Side effects:** `delete_option( 'lsx_wetu_importer_accommodation_settings' )` then `add_option()` with the new content selection. Remote GET to WETU `/Get` endpoint. Calls `import_row()`, `format_completed_row()`, `remove_from_queue()`, `cleanup_posts()` (all of which have further side effects, see below/base class).

#### `import_row( $data, $wetu_id, $id = 0, $team_members = false, $importable_content = array(), $safari_brands = false )`
- **Visibility:** public, non-static.
- **Params:** `$data` (array, decoded WETU JSON), `$wetu_id` (string), `$id` (int|string post ID, default `0`), `$team_members` (array|false), `$importable_content` (array of selected content-type keys), `$safari_brands` (array|false of term IDs).
- **Description:** The core import routine for a single accommodation record. Builds `post_content`/`post_excerpt` from WETU description fields (`extended_description` → `general_description` → `teaser_description`, with optional HTML stripping controlled by the `disable_accommodation_filtering` option), then either updates an existing `accommodation` post (`wp_update_post`) or inserts a new one (`wp_insert_post`), respecting a `disable_accommodation_title` option that can prevent title overwrites unless `title` is explicitly checked. Dispatches to a series of setter methods based on which content types were selected: attachments/gallery discovery, team member, safari brand taxonomy, map/location data, destination connection, category taxonomy, room data, rating, check-in/out, spoken languages, friendly, special interests, videos, facilities, featured image, banner image, main gallery.
- **Side effects:**
  - **CPT:** `accommodation` post created/updated.
  - **Post meta:** `lsx_wetu_id` (added on insert only), `lsx_wetu_modified_date` (added/updated from `last_modified`).
  - Delegates many further side effects to helper methods listed below and to base-class methods (`find_attachments`, `set_team_member`, `set_map_data`, `set_featured_image`, `set_banner_image`, `create_main_gallery`, `save_custom_field`, `set_term`).
  - No hooks fired directly in this method.

#### `set_safari_brands( $id, $safari_brands )`
- **Visibility:** public, non-static.
- **Params:** `$id` (int post ID), `$safari_brands` (array of term IDs).
- **Description:** Assigns one or more `accommodation-brand` taxonomy terms to the post, additively (append mode).
- **Side effects:** `wp_set_object_terms( $id, ..., 'accommodation-brand', true )` — writes taxonomy relationships. Touches taxonomy `accommodation-brand`.

#### `connect_destinations( $data, $id )`
- **Visibility:** public, non-static.
- **Params:** `$data` (array WETU payload), `$id` (int post ID).
- **Description:** If the WETU payload includes a `position` (geo/location) block, delegates to the base class's `set_destination()` to associate the accommodation with a `destination` post.
- **Side effects:** Indirect, via `set_destination()` (base class) — likely sets a related/connected destination post ID meta or taxonomy.

#### `set_taxonomy_style( $data, $id )`
- **Visibility:** public, non-static.
- **Params:** `$data` (array, expects `$data[0]['category']`), `$id` (int post ID).
- **Description:** Assigns the accommodation "travel style" (`accommodation-type`) term derived from the WETU `category` field.
- **Hooks:** Fires filter `lsx_wetu_importer_accommodation_type_term` on the trimmed category string, letting other code remap/rename category values before term assignment.
- **Side effects:** Calls `$this->set_term( $id, $term, 'accommodation-type' )` — writes a taxonomy term relationship for `accommodation-type`.

#### `set_room_data( $data, $id )`
- **Visibility:** public, non-static.
- **Params:** `$data` (array WETU payload with `rooms`), `$id` (int post ID).
- **Description:** Builds an array of "unit" (room) records — title, stripped description, price (hardcoded `0`), type (`room`), and up to 2 attached gallery images per room (via `attach_image()`) — then replaces the post's `units` meta with this array, and sets/updates a `number_of_rooms` meta value (from `features.rooms` count if present, otherwise counted from the rooms array).
- **Side effects:**
  - Downloads/attaches media via `$this->attach_image()` (sideloads images as WP attachments attached to the post).
  - Post meta: deletes then re-adds `units` (array, serialized); adds or updates `number_of_rooms`.

#### `set_rating( $data, $id )`
- **Visibility:** public, non-static.
- **Params:** `$data` (array WETU payload with `features.star_authority`/`features.stars`), `$id` (int post ID).
- **Description:** Determines the rating authority type (defaults to `'Unspecified2'` if absent) and numeric star rating, saving both via the base class's `save_custom_field()`.
- **Side effects:** Post meta `rating_type` and `rating` (numeric, third arg `true` likely indicates "is numeric"/overwrite behavior in `save_custom_field`).

#### `set_spoken_languages( $data, $id )`
- **Visibility:** public, non-static.
- **Description:** Iterates `features.spoken_languages` from WETU data and saves each as a `spoken_languages` custom field (non-unique, appended) using sanitized-title values.
- **Side effects:** Post meta `spoken_languages` (multiple values, one per language) via `save_custom_field()`.

#### `set_friendly( $data, $id )`
- **Visibility:** public, non-static.
- **Description:** Iterates `features.suggested_visitor_types` and saves each as a `suggested_visitor_types` custom field entry.
- **Side effects:** Post meta `suggested_visitor_types` (multiple values).

#### `set_special_interests( $data, $id )`
- **Visibility:** public, non-static.
- **Description:** Iterates `features.special_interests` and saves each as a `special_interests` custom field entry.
- **Side effects:** Post meta `special_interests` (multiple values).

#### `set_checkin_checkout( $data, $id )`
- **Visibility:** public, non-static.
- **Description:** Parses `features.check_in_time` / `features.check_out_time` (WETU format uses `h` as a time separator, e.g. `14h00`), reformats to `h:ia` (e.g. `2:00pm`), and saves them as custom fields.
- **Side effects:** Post meta `checkin_time`, `checkout_time`.

#### `set_facilities( $data, $id )`
- **Visibility:** public, non-static.
- **Description:** For each of four fixed facility groups (`available_services`, `property_facilities`, `room_facilities`, `activities_on_site`), creates/finds a parent `facility` term (labelled "Available Services", "Property Facilities", "Room Facilities", "Activities on Site") and attaches each child facility from the WETU data as a child term under that parent, associated with the post.
- **Side effects:** Taxonomy `facility` — creates/uses hierarchical terms (parent + children) and assigns them to the post via `set_term()`. Note: potential bug — `$parent_id` from a prior loop iteration could persist if `$data[0]['features'][$key]` isn't set for a given `$key` (no `isset` guard around the inner `foreach`), and `$parent_id` is not reset per iteration if the parent block is missing.

