# `WETU_Automation`

**Files:** `classes/class-wetu-automation.php` **and** `bin/class-wetu-automation.php`

## ⚠️ Two copies of the same class

These are **near-duplicate** versions of the same class (`\lsx\wetu_importer\classes\WETU_Automation`), not two genuinely different classes despite both being full implementations. Both live in namespace `lsx\wetu_importer\classes`, both wire up Action Scheduler-based sync jobs, and share ~95% identical code.

Per the plugin bootstrap (`lsx-importer-for-wetu.php` → `classes/class-lsx-wetu-importer.php`), only files under `classes/` are ever `require`d — **`classes/class-wetu-automation.php` is the active, in-use version**. `bin/class-wetu-automation.php` appears to be a stale/legacy development copy retained for reference and is **not loaded by the plugin**. See [Known Issues](known-issues.md) for the specific behavioral differences (including an unauthenticated debug backdoor in the `bin/` copy) — this file documents the canonical `classes/` version.

## Overall responsibility

Orchestrates scheduled, queue-based synchronization of Tour, Accommodation, Destination, and "pin" content from the Wetu API into WordPress, using the [Action Scheduler](https://actionscheduler.org/) library (`as_enqueue_async_action`) for background processing instead of doing everything inline/synchronously. Acts as the automation/cron layer sitting on top of the interactive importer classes (`LSX_WETU_Importer_Tours`, `LSX_WETU_Importer_Accommodation`, `LSX_WETU_Importer_Destination`), reusing their `import_row()` methods rather than duplicating mapping logic.

## Properties

| Property | Type | Default | Visibility | Static |
|---|---|---|---|---|
| `$instance` | object | `null` | protected | yes — singleton instance holder |

## Methods

### `__construct()`
Visibility: private (enforces singleton). Hooks `init` → `init()`.

### `get_instance()`
`public static function get_instance()` — standard singleton accessor.

### `init()`
`public function init()`
If Action Scheduler (`as_enqueue_async_action`) is available, registers the core custom action hooks:
- `lsx_wetu_master_sync` → `run_main_actions()`
- `lsx_wetu_sync_tour` → `tour_sync_action()` (1 arg)
- `lsx_wetu_sync_que` → `que_sync_action()`
- `lsx_wetu_sync_pin` → `pins_sync_action()` (2 args)

**Note:** the call to `schedule_main_action()` is commented out in this file, so the daily cron event is not actually (re-)scheduled from here in this version — the `lsx_wetu_master_sync` hook must be scheduled elsewhere or manually.

### `schedule_main_action()`
`public function schedule_main_action()`
If not already scheduled, registers a daily WP-Cron event `lsx_wetu_master_sync` starting at `01:00:00` via `wp_schedule_event()`.
- **Side effects:** WP-Cron scheduling.

### `run_main_actions()`
`public function run_main_actions()` — the master sync entry point (fired by the `lsx_wetu_master_sync` cron action).
Fetches all current `tour` posts (`get_current_tours()`) and all tours known to Wetu (`get_wetu_tours()`). For each local tour: reads `lsx_wetu_id`/`lsx_wetu_modified_date` meta; if the Wetu-side tour's `last_modified` is not newer than the locally stored modified date, logs a skip (via `ActionScheduler_Logger` if present) and continues without enqueuing a resync; otherwise updates the local `lsx_wetu_modified_date` meta and enqueues an async `lsx_wetu_sync_tour` action (group `lsx_wetu_sync`) with `tour_id`/`overrides`, recording the returned action ID in `lsx_wetu_sync_action_id` post meta. Afterward enqueues a single `lsx_wetu_sync_que` async action to process the accommodation/destination queue.
- **Side effects:** Calls the Wetu API indirectly via `get_wetu_tours()`; updates `lsx_wetu_modified_date` and `lsx_wetu_sync_action_id` post meta; enqueues Action Scheduler actions.
- Contains large blocks of commented-out legacy code (auto-creating new WP posts for un-imported Wetu tours, and a pins-sync loop) retained for reference but not executed.

### `get_current_tours()`
`public function get_current_tours()` → `array`
Runs a `WP_Query` for published `tour` posts having a `lsx_wetu_id` meta key (`fields => 'ids'`, unlimited `posts_per_page`).

### `get_pins()`
`public function get_pins()` → `array`
Runs a `WP_Query` for published `accommodation`/`destination` posts having `lsx_wetu_id` meta (`fields => 'ids'`, limited to 10 posts — hardcoded, likely for testing). Not currently called anywhere in this file except in commented-out code.

### `get_wetu_tours()`
`public function get_wetu_tours()` → `array`
Calls the Wetu Itinerary List API (`https://wetu.com/API/Itinerary/{api_key}/V8/List?type=Personal&own=true&results=10000`) once per entry in a hardcoded `$tags = array( 'Website' )` loop (though the `$tag` value is not actually used in the request URL). Parses the JSON response and builds an associative array keyed by `identifier` of itinerary data.
- **Side effects:** External HTTP GET request(s) to wetu.com per loop iteration.

### `tour_sync_action( $tour_id, $overrides = array() )`
Action callback for `lsx_wetu_sync_tour`.
Builds a default `$importable` list of tour fields to sync (price, description, duration, group_size, category, itineraries, start/end point, itinerary description/included/excluded, room/drinks basis, accommodation, destination); adds `featured_image`/`banner_image` if the tour has no thumbnail yet; allows `$overrides` to fully replace the list; applies the `wetu_automation_tour_flags` filter. If a valid `lsx_wetu_id` exists, fetches the tour detail from the Wetu Itinerary Get API, decodes it, and delegates the actual import to a new `\LSX_WETU_Importer_Tours` instance's `import_row()`, then calls `attach_destination_images()` and `cleanup_posts()` on it. On success, calls `save_queue()`, and refreshes the `lsx_wetu_modified_date` meta to "now".
- **Hooks:** Applies `wetu_automation_tour_flags` filter on the importable fields array.
- **Side effects:** External HTTP GET to Wetu Itinerary API; delegates DB writes to `LSX_WETU_Importer_Tours::import_row()`; updates `lsx_wetu_modified_date` post meta.
- Returns `bool`.

### `que_sync_action()`
Action callback for `lsx_wetu_sync_que`.
Reads the `lsx_wetu_importer_que` option (an array of queued item IDs), and for each queued item enqueues an async `lsx_wetu_sync_pin` action (with `gallery => true`), recording the resulting action ID in `lsx_wetu_sync_action_id` post meta. Then deletes the `lsx_wetu_importer_que` option entirely.
- **Side effects:** Reads/deletes the `lsx_wetu_importer_que` option; enqueues Action Scheduler actions; writes `lsx_wetu_sync_action_id` post meta.

### `pins_sync_action( $item_id = '', $gallery = false )`
Action callback for `lsx_wetu_sync_pin`.
For a given accommodation/destination `$item_id`, fetches its Wetu "pin" data from the Wetu Pins API (**note:** uses a hardcoded API key literal `ROARLEMOUP5IENOE` in the URL rather than `get_api_key()` — see [Known Issues](known-issues.md)), compares `last_modified` against the stored `lsx_wetu_modified_date` meta to decide whether to skip; if not skipped, builds an `$importable` list (adding `featured_image`/`banner_image` if no thumbnail, `gallery` if none exists yet), then dispatches to either `\LSX_WETU_Importer_Accommodation` or `\LSX_WETU_Importer_Destination` based on `get_post_type( $item_id )`, calling `import_row()` then `remove_from_queue()` and finally `cleanup_posts()`.
- **Side effects:** External HTTP GET to Wetu Pins API; updates/deletes `lsx_wetu_modified_date` post meta; delegates DB writes to the relevant importer class.
- Returns `bool`.

### `get_destination_flags( $flags = array() )`
Appends a fixed list of destination-related import flags (location, country, continent, electricity, banking, cuisine, climate, transport, dress, health, safety, visa, additional_info) to the passed-in `$flags` array, then applies the `wetu_automation_destination_flags` filter.

### `get_accommodation_flags( $flags = array() )`
Appends a fixed list of accommodation-related import flags (description, excerpt, category, location, destination, rating, rooms, checkin, facilities, friendly, special_interests, spoken_languages) to `$flags`, then applies the `wetu_automation_accommodation_flags` filter.

### `get_api_key()`
`public function get_api_key()` → `string`
Returns the Wetu API key: uses the `WETU_API_KEY` constant if defined, otherwise reads `api_key` from `lsx_wetu_get_options()` (plugin settings option). Returns `false` if no key is configured.

**End of file:** `WETU_Automation::get_instance();` bootstraps the singleton immediately on file load.

## Differences in `bin/class-wetu-automation.php` (not loaded by the plugin)

- No `ABSPATH` exit guard at the top of the file.
- `__construct()` also hooks `tester_init()` on `init`, adding **unauthenticated `$_GET`-triggered debug entry points**: `wetu_main_debug` (calls `run_main_actions()` then `die()`), `wetu_tour_debug` (calls `tour_sync_action( $_GET['wetu_tour_debug'] )` then `die()`), `wetu_pin_debug` (calls `pins_sync_action( $_GET['wetu_pin_debug'], false )` then `die()`) — no capability/nonce check. See [Known Issues](known-issues.md).
- `init()` actively calls `schedule_main_action()` (not commented out), and additionally sets `$this->options`/`$this->api_key` as dynamic properties.
- Has no `get_api_key()` method; relies on `$this->api_key` set in `init()`.
- `get_wetu_tours()` appends `tags={$tag}` to the API URL (using the loop variable) and omits `results=10000`.
- `run_main_actions()` has the `isset( $wetu_tours[ $wetu_id ] )` guard commented out — a regression that can cause a null-index warning.
- `tour_sync_action()` references an undefined `$flags` variable in `apply_filters( 'wetu_automation_tour_flags', $flags )` (should be `$importable`), and performs a redundant unused `wp_remote_get()` call before the real fetch. Updates a different meta key (`lsx_wetu_sync_date`) on success instead of `lsx_wetu_modified_date`.
- `pins_sync_action()` correctly uses `$this->api_key` (the configured key) rather than the hardcoded literal found in the `classes/` version — ironically more correct on this one point.

`get_current_tours()`, `get_pins()`, `que_sync_action()`, `get_destination_flags()`, and `get_accommodation_flags()` are functionally identical between the two files.
