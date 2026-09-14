# `LSX_WETU_Importer`

**File:** `classes/class-lsx-wetu-importer.php`
**Extends:** none (base/bootstrap class; not abstract)

## Overall responsibility

`LSX_WETU_Importer` is the main bootstrap and shared base class for the plugin. It is instantiated as a global singleton at the bottom of the file (`$lsx_wetu_importer = LSX_WETU_Importer::get_instance();`), and it:

- Loads plugin options/settings, the API key, and image-scaling configuration.
- Registers the WordPress admin hooks that render the importer screen under **Tools → Import**.
- Requires and wires up the other importer classes (`LSX_WETU_Importer_Welcome`, `LSX_WETU_Importer_Accommodation`, `LSX_WETU_Importer_Destination`, `LSX_WETU_Importer_Tours`, `LSX_WETU_Importer_Settings`, `WETU_Automation`, optionally `LSX_WETU_Importer_Post_Columns`), and selects which one (`$current_importer`) handles the current request based on the `tab` query var.
- Acts as a **shared base/utility class**: the tab-specific classes (`Accommodation`, `Destination`, `Tours`, `Banner_Integration`) extend it directly to reuse its helper methods (`save_custom_field`, `attach_image`, `set_map_data`, `set_destination`, `set_country`, `queue_item`, etc.).
- Proxies AJAX requests (`process_ajax_search`, `process_ajax_import`) to whichever `$current_importer` is active.
- Handles all Wetu REST API communication for destination/country auto-creation (`wp_remote_get` calls to `https://wetu.com/API/Pins/...`), image sideloading from Wetu's `ImageHandler` CDN, and gallery/featured-image/banner-image management.
- Maintains an import queue system (`lsx_wetu_importer_que` option) so that importing a tour automatically queues its related accommodation/destinations for subsequent sync.

## Constants

None defined in this file (relies on externally-defined constants: `LSX_WETU_IMPORTER_PATH`, `LSX_WETU_IMPORTER_URL`, `LSX_WETU_IMPORTER_VER`, `LSX_WETU_IMPORTER_CORE`, `WETU_API_KEY`).

## Properties

| Property | Visibility | Static | Type / Default | Description |
|---|---|---|---|---|
| `$instance` | protected | static | `object\|null`, default `null` | Holds the singleton instance of the class. |
| `$plugin_slug` | public | no | `string`, default `'lsx-wetu-importer'` | Slug used for the importer registration and asset enqueue checks. |
| `$tab_slug` | public | no | `string`, default `'default'` | Current active tab (`accommodation`, `destination`, `tour`, `settings`, or `default`), derived from `$_GET['tab']`/`$_POST['type']`. |
| `$options` | public | no | `array\|false`, default `false` | Plugin options retrieved via `lsx_wetu_get_options()`. |
| `$import_scaling_url` | public | no | `string\|false`, default `false` | Declared but the actual URL is written to a dynamic `$image_scaling_url` property instead (see [Known Issues](known-issues.md)). |
| `$scale_images` | public | no | `bool`, default `false` | Whether image scaling is enabled per plugin options. |
| `$api_key` | public | no | `string\|false`, default `false` | The Wetu API key, sourced from the `WETU_API_KEY` constant or plugin options. |
| `$post_types` | public | no | `array`, default `array()` | The post types the importer supports (`accommodation`, `destination`, `tour`). |
| `$found_attachments` | public | no | `array`, default `array()` | Cache of already-attached image attachment IDs keyed to filenames, used to avoid re-downloading duplicate images. |
| `$attachment_urls` | public | no | `array`, default `array()` | Declared but unused elsewhere in this file. |
| `$gallery_meta` | public | no | `array`, default `array()` | Maps attachment ID → image URL for the gallery custom field. |
| `$cleanup_posts` | public | no | `array`, default `array()` | Map of post ID → meta key that needs deduplication via `cleanup_posts()`. |
| `$relation_meta` | public | no | `array`, default `array()` | Map of destination/country post relationships (child → parent) used to correct hierarchy later. |
| `$image_limit` | public | no | `int\|false`, default `false` | Maximum number of gallery images to import, from plugin options. |
| `$featured_image` | public | no | `int\|false`, default `false` | Attachment ID set as the featured image for the current import. |
| `$banner_image` | public | no | `int\|false`, default `false` | Attachment ID set as the banner image for the current import. |
| `$current_importer` | public | no | `object\|false`, default `false` | Instance of the currently active tab-specific importer class. |
| `$queued_imports` | public | no | `array`, default `array()` | Items previously queued for import, loaded from the `lsx_wetu_importer_que` option. |
| `$import_queue` | public | no | `array`, default `array()` | Items queued during the current request, to be persisted via `save_queue()`. |
| `$current_post` | public | no | `int\|false`, default `false` | Holds the post currently being imported (for content/excerpt checks). |
| `$accommodation_settings` | public | no | `mixed\|false`, default `false` | Accommodation-specific settings loaded from `_lsx-to_settings` option. |
| `$tour_settings` | public | no | `mixed\|false`, default `false` | Tour-specific settings loaded from `_lsx-to_settings` option. |
| `$destination_settings` | public | no | `mixed\|false`, default `false` | Destination-specific settings loaded from `_lsx-to_settings` option. |
| `$debug_enabled` | public | no | `bool\|int`, default `false` | Flag for whether debug mode is enabled (declared, not set elsewhere in this file). |
| `$post_columns` | public | no | `object\|false`, default `false` | Instance of `LSX_WETU_Importer_Post_Columns`, if the "enable tour ref column" option is on. |

## Methods

### `__construct()`

- `public function __construct()`
- Bootstraps the plugin: registers the PHP-version compatibility check, loads helper functions, initializes plugin variables, hooks in text-domain loading/admin scripts/menu registration, requires the other importer class files, conditionally instantiates the post-columns helper, and (when not on the default tab) wires up the AJAX search/import actions.
- **Hooks registered:**
  - `add_action( 'admin_init', [ $this, 'compatible_version_check' ] )`
  - `add_action( 'init', [ $this, 'load_plugin_textdomain' ] )`
  - `add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ], 11 )`
  - `add_action( 'admin_menu', [ $this, 'register_importer_page' ], 200 )`
  - `add_action( 'init', [ $this, 'load_class' ] )`
  - Conditionally (if `$this->tab_slug !== 'default'`): `wp_ajax_lsx_tour_importer`, `wp_ajax_nopriv_lsx_tour_importer`, `wp_ajax_lsx_import_items`, `wp_ajax_nopriv_lsx_import_items`
- **Side effects:** Requires 6 class files from disk; conditionally instantiates `LSX_WETU_Importer_Post_Columns` singleton.

### `get_instance()`

- `public static function get_instance()`
- Classic singleton accessor — creates the single `LSX_WETU_Importer` instance on first call and returns it thereafter.

### `load_plugin_textdomain()`

- `public function load_plugin_textdomain()`
- Loads the plugin's translation files for the `lsx-wetu-importer` text domain. Hooked to `init`.

### `set_variables()`

- `public function set_variables()`
- Populates most of the plugin's runtime state: supported post types, options (via `lsx_wetu_get_options()`), per-post-type settings (from `_lsx-to_settings` option), the API key (constant or option), the active `tab_slug` (from `$_GET['tab']` or AJAX `$_POST['type']`), the queued-imports list (from `lsx_wetu_importer_que` option), and image-scaling settings/URL.
- **Side effects:** Reads options `_lsx-to_settings`, `lsx_wetu_importer_que`; reads `$_GET`/`$_POST` superglobals (sanitized). No writes.

### `register_activation_hook()`

- `public static function register_activation_hook()`
- Entry point hooked to `register_activation_hook()` in the main plugin file; delegates to the activation-time compatibility check.

### `compatible_version()`

- `public static function compatible_version()`
- Returns `true`/`false` depending on whether the running PHP version is >= 5.6.

### `compatible_version_check()`

- `public function compatible_version_check()`
- Runtime safety net hooked to `admin_init`; if the PHP version becomes incompatible after activation, deactivates the plugin and displays an admin notice.
- **Side effects:** Calls `deactivate_plugins()`; unsets `$_GET['activate']`; conditionally registers `add_action( 'admin_notices', [ $this, 'compatible_version_notice' ] )`.

### `compatible_version_notice()`

- `public function compatible_version_notice()`
- Prints the admin-notice HTML warning that the plugin requires PHP 5.6+.

### `compatible_version_check_on_activation()`

- `public static function compatible_version_check_on_activation()`
- Called during plugin activation; deactivates the plugin and calls `wp_die()` with an explanatory message if the PHP version is incompatible.
- **Side effects:** `deactivate_plugins()`, `wp_die()` (halts execution).

### `load_class()`

- `public function load_class()`
- Instantiates the correct tab-specific object into `$this->current_importer` based on `$this->tab_slug` (`accommodation`, `destination`, `tour`, `settings`, default → Welcome screen). Hooked to `init`.

### `register_importer_page()`

- `public function register_importer_page()`
- Registers the plugin with WordPress's built-in importer system via `register_importer()`, so it appears under **Tools → Import** as "Wetu Content Importer", using `display_page()` as the render callback. Hooked to `admin_menu` (priority 200).

### `admin_scripts()`

- `public function admin_scripts()`
- Conditionally enqueues the plugin's CSS/JS (including DataTables) only when on the importer's own admin screen (`$_GET['import'] === $this->plugin_slug`), and localizes the AJAX URL + nonce (`lsx_wetu_ajax_action`) for the JS to use. Hooked to `admin_enqueue_scripts` (priority 11).
- **Side effects:** `wp_enqueue_style`/`wp_enqueue_script`/`wp_localize_script`; creates a nonce via `wp_create_nonce()`.

### `display_page()`

- `public function display_page()`
- Renders the main importer admin page wrapper: navigation tabs, (conditionally) the Wetu connection status and post-status sub-navigation, then delegates to `$this->current_importer->display_page()` for the tab-specific content.

### `post_status_navigation()`

- `public function post_status_navigation()`
- Renders the "Search / Published / Pending / Draft / WETU (Queue)" sub-navigation links with post counts (via helper functions `lsx_wetu_get_post_count()`, `lsx_wetu_get_tour_count()`, `lsx_wetu_get_queue_count()`), plus a documentation link.

### `search_form()`

- `public function search_form()`
- Renders the AJAX search form markup (single keyword + bulk keyword textarea + search-logic radio buttons) used to query Wetu for items to import.
- **Hooks:** Fires `do_action( 'lsx_wetu_importer_search_form', $this )` to let extensions inject additional form fields.

### `table_header()` / `table_footer()`

- `public function table_header()` / `public function table_footer()`
- Output the `<thead>`/`<tfoot>` markup for the results/import data table (checkbox, order, title, date, Wetu ID columns).

### `navigation( $tab = '' )`

- `public function navigation( $tab = '' )`
- Renders the top-level tab navigation (Home / Tours / Accommodation / Destinations / Settings) with the current tab highlighted, using `itemd()` to compute the "current" CSS class.

### `wetu_status()`

- `public function wetu_status()`
- Displays the "Wetu Status" connection indicator; if the `lsx_ti_tours` transient is empty/expired or a manual refresh (`$_GET['refresh_tours']`) is requested, it calls `update_options()` to re-fetch from the Wetu API and shows Connected/error state accordingly.
- **Side effects:** Reads the `lsx_ti_tours` transient; may trigger a live API call via `update_options()`.

### `team_member_checkboxes( $selected = array() )`

- `public function team_member_checkboxes( $selected = array() )`
- If the `team` post type exists (LSX Team plugin integration), renders a checkbox list of all published team members for associating with an imported item.
- **Side effects:** Runs a `WP_Query` for `team` posts.

### `checked( $haystack = false, $needle = '', $echo = true )` / `selected( $haystack = false, $needle = '', $echo = true )`

- Convenience wrappers around `itemd()` that output/return a `checked="checked"` / `selected="selected"` attribute if `$needle` is found in `$haystack`.

### `itemd( $haystack = false, $needle = '', $type = '', $wrap = true )`

- `public function itemd( $haystack = false, $needle = '', $type = '', $wrap = true )`
- Core shared helper used by `checked()`/`selected()`/`navigation()`: normalizes `$haystack` to an array, checks if `$needle` is present, and returns either a full HTML attribute string (`type="type"`) or the bare type string depending on `$wrap`.

### `find_attachments( $id = false )`

- `public function find_attachments( $id = false )`
- Populates `$this->found_attachments` with existing attachment posts belonging to `$id` (post parent), keyed by attachment ID and stripped-of-extension filename, so later image-import logic can detect duplicates and skip re-downloading.
- **Side effects:** Runs a `WP_Query` for `attachment` post type with `post_parent = $id`.

### `save_custom_field( $value = false, $meta_key = '', $id = 0, $decrease = false, $unique = true )`

- `public function save_custom_field( $value = false, $meta_key = '', $id = 0, $decrease = false, $unique = true )`
- Generic post-meta save helper. Optionally decrements an integer value first; if the meta key represents a Tour Operator "connection" field (checked against `tour_operator()->legacy->admin->connections`), delegates to `save_merged_field()` to merge/append rather than overwrite; otherwise does a normal `update_post_meta()` or `add_post_meta()` depending on `$unique`.
- **Side effects:** `get_post_meta()`, `update_post_meta()`, or `add_post_meta()` writes; calls the global `tour_operator()` function from the Tour Operator plugin.

### `save_merged_field( $value, $meta_key, $id, $prev )`

- `public function save_merged_field( $value, $meta_key, $id, $prev )`
- Merges a new post ID into an existing array-type connection meta field (e.g., accommodation-to-destination relationships) rather than overwriting it, deduplicating via `array_unique()`.
- **Side effects:** `add_post_meta()` or `update_post_meta()` writes.

### `cleanup_posts()`

- `public function cleanup_posts()`
- Iterates `$this->cleanup_posts` (post ID → meta key pairs accumulated during import) and deduplicates the corresponding repeating post-meta values, deleting and re-adding only unique entries.
- **Side effects:** `get_post_meta()`, `delete_post_meta()`, multiple `add_post_meta()` writes.

### `set_taxonomy( $taxonomy, $terms, $id )`

- `public function set_taxonomy( $taxonomy, $terms, $id )`
- Intended to bulk-assign/create taxonomy terms for a post. **Bug:** the method body references undefined variables `$data` and `$tax` instead of its own `$terms`/`$taxonomy` parameters — see [Known Issues](known-issues.md).

### `set_term( $id = false, $name = false, $taxonomy = false, $parent = false )`

- `public function set_term( $id = false, $name = false, $taxonomy = false, $parent = false )`
- Finds or creates a single taxonomy term (optionally under a parent term) and attaches it to post `$id` via `wp_set_object_terms()`.
- **Side effects:** `term_exists()`, `wp_insert_term()`, `wp_set_object_terms()`; echoes an error message if term creation fails.

### `taxonomy_checkboxes( $taxonomy = false, $selected = array() )`

- `public function taxonomy_checkboxes( $taxonomy = false, $selected = array() )`
- Builds and returns an HTML `<ul>` of checkboxes for every term in the given taxonomy, marking any in `$selected` as checked.
- **Side effects:** `get_terms()` query.

### `set_map_data( $data, $id, $zoom = '10' )`

- `public function set_map_data( $data, $id, $zoom = '10' )`
- Extracts latitude/longitude (preferring "driving" coordinates) and a cleaned address from Wetu API response data, then saves it as the `location` post-meta array (used by the Tour Operator map feature) for the given post.
- **Side effects:** `update_post_meta()`/`add_post_meta()` write of the `location` meta field.

### `set_featured_image( $data, $id )`

- `public function set_featured_image( $data, $id )`
- Takes the first image in the Wetu content payload, downloads/attaches it via `attach_image()`, and sets it as the post's featured image (`_thumbnail_id`).
- **Side effects:** Calls `attach_image()` (network request + `media_handle_sideload`); `delete_post_meta()` + `add_post_meta()` for `_thumbnail_id`. Sets `$this->featured_image`.

### `set_banner_image( $data, $id, $content = array( 'none' ) )`

- `public function set_banner_image( $data, $id, $content = array( 'none' ) )`
- Attaches a banner image — either a unique "destination_image" (if `'unique_banner_image'` is in `$content` and available) or the second image in the content images array — and stores it in the `image_group` post-meta structure (CMB2 field format) used for the banner display.
- **Side effects:** Calls `attach_image()`; `delete_post_meta()` + `add_post_meta()` for `image_group`. Sets `$this->banner_image`.

### `is_image_being_used( $image_id = '', $post_id = '' )`

- `public function is_image_being_used( $image_id = '', $post_id = '' )`
- Checks whether an attachment is set as `_thumbnail_id` on any post other than `$post_id`, to prevent deleting an image that's still in use elsewhere (used before deleting stale gallery images during replacement).
- **Side effects:** Direct raw SQL query against `$wpdb->postmeta` (phpcs-suppressed direct DB query).

### `create_main_gallery( $data, $id )`

- `public function create_main_gallery( $data, $id )`
- Builds the post's `gallery` meta field from the Wetu content images array. If the "image_replacing" option is enabled, first deletes the existing gallery images (and the underlying attachment posts, if not used as a thumbnail elsewhere via `is_image_being_used()`). Skips images already used as featured/banner, and respects `$this->image_limit`.
- **Side effects:** `delete_post_meta()` (repeatedly, and for the whole `gallery` key), `wp_delete_attachment()` (permanent file deletion), calls `attach_image()` for each new image, `add_post_meta()` for the final `gallery` array.

### `get_scaling_url( $args = array() )`

- `public function get_scaling_url( $args = array() )`
- Builds a Wetu `ImageHandler` URL fragment (e.g., `https://wetu.com/ImageHandler/h1024x768/`) using width/height/cropping either from `$args`, plugin options, or hardcoded defaults.

### `attach_image( $v = false, $parent_id = 0, $image_sizes = false, $banner = false )`

- `public function attach_image( $v = false, $parent_id = 0, $image_sizes = false, $banner = false )`
- Given a single Wetu image data array (`$v`), derives a clean filename/title from `url_fragment`, returns an existing attachment ID if already found (and image replacing is off), otherwise builds the scaled Wetu image URL via `get_scaling_url()` and downloads/sideloads it via `attach_external_image2()`, recording the new attachment in `$this->found_attachments` and saving its Wetu URL fragment as `lsx_wetu_id` meta.
- **Side effects:** Calls `attach_external_image2()` (network fetch + media sideload); `add_post_meta()` for `lsx_wetu_id` on the attachment.

### `attach_external_image2( $url = null, $post_id = null, $thumb = null, $filename = null, $post_data = array() )`

- `public function attach_external_image2( $url = null, $post_id = null, $thumb = null, $filename = null, $post_data = array() )`
- Low-level media-sideload routine: downloads a remote image via `wp_remote_get()`, writes it to a temp file via the WP filesystem API, optionally renames it to a sanitized custom filename, and hands it to `media_handle_sideload()` to create the WordPress attachment with custom post data (title, parent, etc.).
- **Side effects:** Network request (`wp_remote_get`); filesystem writes (`tempnam( '/tmp', 'FOO' )`, `$wp_filesystem->put_contents()`, `chmod()`, `move()`); creates a new attachment post via `media_handle_sideload()`; deletes the temp file (`wp_delete_file()`) on failure. Requires WP core admin includes (`file.php`, `media.php`, `image.php`) and initializes `WP_Filesystem()`.
- **See [Known Issues](known-issues.md)** for the hardcoded `/tmp` path and unchecked `preg_match()` result.

### `process_ajax_search()` / `process_ajax_import()`

- AJAX handlers bound to `wp_ajax_lsx_tour_importer`/`_nopriv_` and `wp_ajax_lsx_import_items`/`_nopriv_` respectively; each delegates to `$this->current_importer->process_ajax_search()`/`process_ajax_import()` then calls `die()`.

### `format_completed_row( $response )` / `format_error( $response )`

- Output a single `<li>` row (success checkmark or error icon, with linked title) for the AJAX import results list.

### `multineedle_stripos( $haystack, $needles, $offset = 0 )`

- `public function multineedle_stripos( $haystack, $needles, $offset = 0 )`
- Returns `true` only if every string in the `$needles` array is found (case-insensitively) within `$haystack`; used for "AND" bulk-keyword search matching.

### `set_video_data( $data, $id )`

- `public function set_video_data( $data, $id )`
- Extracts YouTube video entries (title, description, URL) from the Wetu content payload and saves them as the `videos` post-meta array, replacing any prior value.
- **Side effects:** `delete_post_meta()` + `add_post_meta()` for `videos`.

### `set_team_member( $id, $team_members )`

- `public function set_team_member( $id, $team_members )`
- Replaces the `team_to_{tab_slug}` post-meta entries for a post with the supplied array of team member IDs (each added as a separate non-unique meta row).
- **Side effects:** `delete_post_meta()` then repeated `add_post_meta()` calls.

### `shuffle_assoc( &$array )`

- `public function shuffle_assoc( &$array )`
- Shuffles an associative array's key order in place (by shuffling the keys and rebuilding the array), preserving key => value pairs; returns `true`.
- **Side effects:** Mutates the passed-by-reference `$array`.

### `find_current_accommodation( $post_type = 'accommodation' )`

- `public function find_current_accommodation( $post_type = 'accommodation' )`
- Runs a direct SQL join query to fetch up to 5000 published/draft posts of the given type along with their `lsx_wetu_id` meta value, returning a `wetu_id => post_id` lookup map. Used to detect whether an item from Wetu already exists locally.
- **Side effects:** Direct raw SQL query (phpcs-suppressed) with string-interpolated post type.

### `find_current_destinations()`

- `public function find_current_destinations()`
- Convenience wrapper calling `find_current_accommodation( 'destination' )`.

### `set_destination( $day, $id, $leg_counter )`

- `public function set_destination( $day, $id, $leg_counter )`
- Core tour-import logic for resolving/creating a destination linked to a tour "day" entry. Looks up an existing local destination by `destination_content_entity_id`; if not found, calls the Wetu Pins API (`https://wetu.com/API/Pins/{api_key}/Get?ids=...`) to fetch destination details and creates a new draft `destination` post (optionally under a country parent, resolved via `set_country()`). Attaches the destination to the tour (and vice versa) via connection meta fields, records image candidates, and queues the destination for later sync.
- **Side effects:** Network call (`wp_remote_get`); `wp_insert_post()` (creates draft destination posts); multiple `save_custom_field()` meta writes; populates `$this->destination_images`, `$this->relation_meta`, calls `queue_item()`.
- **Note:** references `$this->current_destinations` / `$this->destination_images` as dynamic properties — see [Known Issues](known-issues.md).

### `set_country( $country_wetu_id, $id )`

- `public function set_country( $country_wetu_id, $id )`
- Resolves or creates a country-level `destination` post (via the same "check local first, else call the Wetu Pins API" pattern as `set_destination()`), links it bidirectionally to the tour/post via connection meta, queues it, and returns its post ID.
- **Side effects:** Network call (`wp_remote_get`); `wp_insert_post()` for new country destination posts; `save_custom_field()` writes; calls `queue_item()`.

### `queue_item( $id )`

- `public function queue_item( $id )`
- Adds a post ID to the in-memory `$this->import_queue` array for later persistence.
- **Note:** the `in_array()` duplicate check is dead logic — see [Known Issues](known-issues.md).

### `save_queue()`

- `public function save_queue()`
- Persists the accumulated `$this->import_queue` (merged with any pre-existing `$this->queued_imports`) to the `lsx_wetu_importer_que` option, deduplicated via `array_unique()`.
- **Side effects:** `delete_option()` then `update_option()` on `lsx_wetu_importer_que`.

### `update_options()`

- `public function update_options()`
- Refreshes the cached list of Wetu tours/itineraries by calling the Wetu Itinerary List API (building the URL from the current importer's `url`/`url_qs`, plus `own`/`type` query filters from `$_GET`), applying the `lsx_wetu_tour_refresh_url` filter, and caching the result in the `lsx_ti_tours` transient (4-hour expiry). Also stores which filter options were used in the `lsx_ti_tours_api_options` option. Returns `true` on success or the Wetu error string on failure.
- **Hooks:** Applies filter `lsx_wetu_tour_refresh_url`.
- **Side effects:** `delete_option()` + `add_option()` for `lsx_ti_tours_api_options`; network call; `set_transient( 'lsx_ti_tours', ..., 4 hours )` on success.

### `get_post_id_by_key_value( $wetu_id = false )`

- `public function get_post_id_by_key_value( $wetu_id = false )`
- Looks up a single post ID whose `lsx_wetu_id` post-meta matches the given Wetu ID, via a direct prepared SQL query.

## Global instantiation

At the very end of the file:

```php
$lsx_wetu_importer = LSX_WETU_Importer::get_instance();
```

This creates the plugin's single global instance immediately when the file is loaded, triggering the entire hook-registration cascade described in `__construct()`.
