# `class-lsx-wetu-importer-tours.php`

## Class: `LSX_WETU_Importer_Tours`

**Extends:** `LSX_WETU_Importer` (the shared base importer class — see [class-lsx-wetu-importer.md](class-lsx-wetu-importer.md) for `set_variables()`, `search_form()`, `save_custom_field()`, `find_current_accommodation()`, `find_attachments()`, `attach_image()`, `set_term()`, `set_country()`, `set_team_member()`, `queue_item()`, `save_queue()`, `cleanup_posts()`, `format_completed_row()`, `format_error()`, `multineedle_stripos()`, `shuffle_assoc()`).

**Overall responsibility:** Implements the "Tour" tab of the Wetu Content Importer admin screen. It renders the tour search/import UI, handles the AJAX search of cached Wetu tour data, fetches individual tour ("Itinerary") records from the Wetu API, and maps that JSON payload onto a WordPress `tour` custom post type — including custom fields (price, duration, group size, reference number), taxonomies (`travel-style`), itinerary "day" repeater data, linked `accommodation` and `destination` posts, map/KML point data, and featured/banner images pulled from destination image pools.

### Properties

| Property | Type / Default | Description |
|---|---|---|
| `$tab_slug` | `string`, `'tour'` | Identifies this importer tab; used to match AJAX requests (`$_POST['type']`) and to render the correct admin tab content. |
| `$url` | `string\|false`, `false` | Base Wetu API URL for itineraries, built in `set_variables()` as `https://wetu.com/API/Itinerary/{api_key}`. |
| `$url_qs` | `string\|false`, `false` | Query-string suffix appended to `$url` (currently always empty string once set). |
| `$current_accommodation` | array once populated, `false` | Cache of existing accommodation posts keyed by Wetu `content_entity_id`, populated via `find_current_accommodation()` (inherited) inside `set_accommodation()`. |
| `$current_destinations` | array once populated, `false` | Reserved for caching current destination posts. |
| `$destination_images` | array once populated, `false` | Map of tour ID → candidate destination image identifiers, consumed by `attach_destination_images()` to pick a featured/banner image for the tour. |
| `$options` | array once populated, `false` | Plugin-wide settings controlling feature toggles such as `disable_tour_title`, `disable_tour_descriptions`, `disable_tour_tags`, `enable_tour_featured_random`. |
| `$tour_options` | array once populated, `false` | The last-used "content to sync" checkbox selections, persisted in the `lsx_wetu_importer_tour_settings` option and reloaded in `set_variables()`. |

### Methods

#### `__construct()`
Constructor; calls `set_variables()` to initialize instance state when the class is instantiated (typically once per admin page load for the Tours tab).

#### `set_variables()`
Extends the parent's `set_variables()` to build the Wetu Itinerary API URL from the shared `$api_key`, and to load any previously-saved "content to sync" checkbox selections from the `lsx_wetu_importer_tour_settings` option into `$tour_options`.
- **Side effects:** Reads the `lsx_wetu_importer_tour_settings` option.

#### `display_page()`
Renders the full admin markup for the Tour importer tab — the search form, the "refresh from Wetu" form, the search-results table (populated later via AJAX/JS), the "content to sync" checkbox panel (title, description, price, duration, group size, category, itinerary days, start/end point, itinerary description/included/excluded, room/drinks basis, accommodation/destination sync, featured/banner image), an optional Team Member assignment panel (if `LSX_TO_Team` plugin class exists), and a "Completed" results panel linking to the Accommodation import tab.
- Reads `$this->options` values (`disable_tour_title`, `disable_tour_descriptions`, `disable_tour_tags`) to conditionally pre-check certain sync checkboxes. Pure output — no data written.

#### `update_options_form()`
Renders the small "Refresh" form that lets an admin choose between "Personal" and "Sample" Wetu tour lists and trigger a re-fetch of the tour list from the Wetu API (handled elsewhere).
- **Side effects:** Reads the `lsx_ti_tours_api_options` option (defaults to `array('sample')` if unset). Output only.

#### `find_current_tours()`
Queries the database directly for all published `tour` posts that have a `lsx_wetu_id` postmeta value, returning them keyed by lower-cased Wetu identifier. Used to detect whether a Wetu search result already corresponds to an existing local post.
- **Side effects:** Direct `$wpdb->get_results()` SQL query (`SELECT` only, limited to 500 rows) joining `wp_postmeta` and `wp_posts`.

#### `process_ajax_search()`
AJAX handler that searches the cached list of Wetu tours (stored in the `lsx_ti_tours` transient) against one or more keyword phrases (AND/OR logic) or against a post-status filter (`publish`, `pending`, `draft`, `import`), and prints back pre-rendered `<tr>` HTML rows for matches.
- **Side effects:** Verifies the AJAX nonce via `check_ajax_referer( 'lsx_wetu_ajax_action', 'security' )`; reads the `lsx_ti_tours` transient; calls `find_current_tours()`; echoes raw HTML and terminates with `die()`.

#### `format_row( $row = false, $row_key = '' )`
Formats a single Wetu tour search result (or an already-imported tour) into an HTML `<tr>` table row for the admin list, including a checkbox, order number, title/status link, reference number, last-modified date, and Wetu identifier.
- Params: `$row` (array|false), `$row_key` (int|string).

#### `process_ajax_import( $force = false )`
AJAX handler that imports/syncs a single tour: reads the posted Wetu ID, target post ID, team members, and content-type selections; persists the content selections to the `lsx_wetu_importer_tour_settings` option; fetches the tour's full itinerary JSON from the Wetu API (`https://wetu.com/API/Itinerary/V8/Get?id={wetu_id}`, filterable via `lsx_wetu_language`); and on success delegates to `import_row()`, then runs post-import housekeeping.
- Params: `$force` (bool, default `false`) — accepted but not referenced in the method body.
- **Hooks:** Applies filter `lsx_wetu_language`.
- **Side effects:**
  - Verifies AJAX nonce (`lsx_wetu_ajax_action`).
  - `delete_option( 'lsx_wetu_importer_tour_settings' )` then `add_option()` with the newly submitted content selections.
  - `wp_remote_get()` HTTP call to the Wetu Itinerary API.
  - On success: calls `import_row()`, `format_completed_row()`, `save_queue()`, `cleanup_posts()`, `attach_destination_images( $content )`, and `clean_attached_destinations( $return )`.
  - On failure: calls `format_error()`.

#### `clean_attached_destinations( $id )`
Ensures the `destination_to_tour` postmeta entries on a tour are de-duplicated after import, since itinerary processing may attach the same destination multiple times across legs/days.
- **Side effects:** Reads all `destination_to_tour` meta; `delete_post_meta( $id, 'destination_to_tour' )`; re-adds each unique value — a full delete-and-rewrite of that meta key.

#### `import_row( $data, $wetu_id, $id = 0, $team_members = false, $importable_content = array(), $old1 = false, $old2 = false )`
The core mapping function that converts a decoded Wetu Itinerary API JSON payload into a WordPress `tour` post — creating a new post or updating an existing one, setting title/content (respecting the "disable custom title/description" settings), and delegating to specialized setters for reference number, team member, price, duration, group size, travel-style taxonomy terms, start/end destination points, itinerary day processing, and map data — based on which content types were selected for import.
- Params: `$data` (array, decoded JSON), `$wetu_id` (string), `$id` (int/string, default `0` — `0` means "create new"), `$team_members` (array|false), `$importable_content` (array of selected content-type keys), `$old1`/`$old2` (unused legacy parameters).
- **Side effects:**
  - `wp_update_post()` for existing posts or `wp_insert_post()` for new ones (status forced to `publish`).
  - For new posts: `add_post_meta( $id, 'lsx_wetu_id', $wetu_id )` and `add_post_meta( $id, 'lsx_wetu_modified_date', strtotime(...) )`.
  - Calls `set_reference_number()`, `set_team_member()` (inherited, if `team` CPT exists), `set_price()`, `set_duration()`, `set_group_size()`, `set_travel_styles()`, `set_start_end_point()`, `process_itineraries()` (only if `legs` data present and `itineraries` selected), and `set_map_data()` (only if `routes` present and `map` selected).
  - Returns the post ID (new or existing).

#### `process_itineraries( $data, $id, $importable_content )`
Iterates over each "leg" of a tour's itinerary (`$data['legs']`), builds a per-day itinerary array (title, description, featured image, linked accommodation, linked destination, included/excluded, room/drinks basis) covering both "by-nights" and "by-destination" leg structures, and handles the special "Mobile" safari leg type by deriving day-specific destinations/accommodation from `stops`. Persists the compiled day list via `set_itinerary_day()`.
- **Hooks:** Fires action `lsx_wetu_itinerary_complete` (with `$id`) after all legs are processed.
- **Side effects:**
  - Calls `get_current_itinerary_images( $id )` to preserve existing per-day featured images unless "replace_itinerary_images" is selected.
  - `delete_post_meta( $id, 'itinerary' )` (always, to fully rebuild the itinerary repeater meta).
  - `delete_post_meta( $id, 'destination_to_tour' )` if `destination` is in `$importable_content`.
  - `delete_post_meta( $id, 'accommodation_to_tour' )` if `accommodation` is in `$importable_content`.
  - Calls `set_accommodation()` and `set_destination()` (or `get_mobile_destination()` for Mobile legs) per leg/day, which themselves create/link `accommodation`/`destination` posts and queue accommodation for import.
  - Calls `set_itinerary_day()` per leg iteration (writes the accumulated `$days` array as repeated `itinerary` postmeta — called once per leg, so meta is added incrementally across legs).

#### `get_current_itinerary_images( $id = 0 )`
Reads the tour's existing `itinerary` postmeta entries (before they're deleted/rebuilt) and extracts any per-day `featured_image` values, indexed by sequential day counter, so that re-importing a tour doesn't wipe out manually-set day images (unless "Replace Custom Images" was explicitly selected).
- Returns: `array` — map of day-number → featured-image value. Read-only.

#### `set_start_end_point( $data, $id )`
Determines the tour's "departs from" and "ends in" destinations from the first and second-to-last legs of the itinerary, resolves each to a country via `set_country()` (inherited), and stores them as postmeta.
- **Hooks:** Applies filters `lsx_wetu_start_end_args`, `lsx_wetu_departs_from_id`, `lsx_wetu_ends_in_id`.
- **Side effects:** `delete_post_meta( $id, 'departs_from' )` and `delete_post_meta( $id, 'ends_in' )`; conditionally `add_post_meta()` for each (unique meta). Calls `set_country()`.

#### `get_mobile_destination( $day, $leg, $id )`
For "Mobile" safari-type legs, determines which destination applies to a specific itinerary day by matching the day's start day against each `stop`'s arrival/departure day range, then resolves that stop via `set_destination()`.
- Returns: destination reference (post ID or false).

#### `get_mobile_accommodation( $day, $leg, $id )`
Analogous to `get_mobile_destination()` but for accommodation — matches the day against each stop's arrival/departure range (using `<` rather than `<=` for departure day) and resolves the matching stop via `set_accommodation()`. Appears to be an alternative/unused helper (itinerary processing calls `get_mobile_destination()` directly and derives accommodation earlier in the loop instead).
- Returns: accommodation reference (post ID or false). Delegates to `set_accommodation()`, which can create a draft `accommodation` post and queue it.

#### `set_map_data( $data, $id, $zoom = 9 )`
Converts the tour's route point data (semicolon-delimited lat/lng pairs) from the Wetu payload into a space-delimited list of `"lat,lng"` pairs and stores them for use in rendering a map. `$zoom` is accepted but not used in the method body.
- **Side effects:** `delete_post_meta( $id, 'wetu_map_points' )`; then `save_custom_field()` writes the combined point string to `wetu_map_points` (only if points were found).

#### `set_itinerary_day( $day, $id )`
Thin wrapper that persists a compiled itinerary day (or day list) to the `itinerary` postmeta key via `save_custom_field()` (added, non-unique, so multiple calls accumulate repeater rows).

#### `set_reference_number( $data, $id )`
Persists the Wetu-provided reference number to the `lsx_wetu_ref` postmeta key, if present in the payload.

#### `set_price( $data, $id )`
Persists price, price-includes, and price-excludes fields from the Wetu payload to postmeta, stripping non-numeric characters from the price string (unless disabled via filter).
- **Hooks:** Applies filters `lsx_wetu_importer_disable_tour_price_filter`, `lsx_wetu_importer_price_meta_key` (default `'price'`), `lsx_wetu_importer_included_meta_key` (default `'included'`), `lsx_wetu_importer_not_included_meta_key` (default `'not_included'`).

#### `set_duration( $data, $id )`
Extracts the numeric portion of the tour's `days` field and stores it as the `duration` postmeta.

#### `set_group_size( $data, $id )`
Persists the tour's `group_size` field to the `group_size` postmeta key.

#### `set_travel_styles( $id, $data )`
Maps the Wetu tour's `tags` array onto the `travel-style` taxonomy by calling the inherited `set_term()` for each tag.
- **Hooks:** Applies filter `lsx_wetu_importer_tour_travel_styles`.

#### `set_accommodation( $day, $id )`
Given a leg/day/stop entry containing a Wetu `content_entity_id`, finds or creates the corresponding `accommodation` post (draft status if new), links it bidirectionally to the tour via `accommodation_to_tour` / `tour_to_accommodation` postmeta, and queues the accommodation post for further/deferred import processing. Explicitly skips a hardcoded blacklisted entity ID (`25862`).
- Returns: the accommodation post ID (or `false`/empty if not applicable).
- **Side effects:**
  - Populates `$this->current_accommodation` via `find_current_accommodation()` — a DB lookup each call.
  - `wp_insert_post()` to create a draft `accommodation` post if none exists for the entity ID.
  - `save_custom_field()` writes: `lsx_wetu_id` on the new accommodation post; `accommodation_to_tour` meta on the tour (non-unique); `tour_to_accommodation` meta on the accommodation post (non-unique).
  - `queue_item( $ac_id )` — adds the accommodation post to an internal import queue (later persisted via `save_queue()`).

#### `attach_destination_images( $importable_content = array() )`
For each tour queued in `$this->destination_images` (tour ID → candidate destination list), randomly shuffles the destination options and, for the first destination that yields usable image data from the Wetu Pins API, sets the tour's featured image and/or banner image depending on which content types were selected.
- **Side effects:**
  - Calls `shuffle_assoc()` and PHP `shuffle()` to randomize ordering.
  - `wp_remote_get()` to `https://wetu.com/API/Pins/{api_key}/Get?ids={destination_id}` for each candidate destination.
  - Calls `find_attachments()`, `set_featured_image()`, and/or `set_banner_image()`, which perform media library writes.

#### `set_featured_image( $data, $id )`
Picks the first not-already-used image from a destination's image list (optionally shuffled if `enable_tour_featured_random` setting is `on`) and sets it as the tour's featured image (`_thumbnail_id`).
- Returns: `bool` — whether an image was set.
- **Side effects:** Reads `$this->options['enable_tour_featured_random']`; calls `check_if_image_is_used()` and `attach_image()`; `delete_post_meta()` then `add_post_meta()` for `_thumbnail_id`.

#### `set_banner_image( $data, $id, $content = array( 'none' ) )`
Picks an unused image (skipping the very first image in the array, presumably reserved for the featured image) from a destination's image list and sets it as the tour's "banner image" via a CMB2-style `image_group` meta structure, sized 1920x600 with center cropping. `$content` is accepted but unused in the method body.
- Returns: `bool` — whether an image was set.
- **Side effects:** Calls `check_if_image_is_used()` and `attach_image()`; `delete_post_meta()` then `add_post_meta( $id, 'image_group', ... )`.

#### `check_if_image_is_used( $v )`
Checks whether a given Wetu image/entity identifier is already associated with any existing `tour` post (via the `lsx_wetu_id` meta key), used to avoid reusing the same destination image across multiple tours.
- Returns: `bool`. Direct `$wpdb->get_results()` prepared `SELECT` query against `wp_postmeta`; read-only.

#### `table_header()` / `table_footer()`
Output the `<thead>`/`<tfoot>` markup for the tour list table (select-all checkbox, Order, Title, Ref, Date, WETU ID columns).
