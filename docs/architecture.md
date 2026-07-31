# Architecture Overview

## Plugin bootstrap

`lsx-importer-for-wetu.php` is the plugin's main file. It:

1. Defines constants: `LSX_WETU_IMPORTER_PATH`, `LSX_WETU_IMPORTER_CORE`, `LSX_WETU_IMPORTER_URL`, `LSX_WETU_IMPORTER_VER`.
2. Registers `register_activation_hook()` → `LSX_WETU_Importer::register_activation_hook()`.
3. Requires `classes/class-lsx-wetu-importer.php`, which at the bottom of the file instantiates the plugin's single global instance:

   ```php
   $lsx_wetu_importer = LSX_WETU_Importer::get_instance();
   ```

   That single line triggers the entire hook-registration cascade for the whole plugin.

## Class map

```
LSX_WETU_Importer (base/bootstrap + shared helpers, singleton)
├── LSX_WETU_Importer_Welcome            (singleton, "Home" tab)
├── LSX_WETU_Importer_Accommodation      (extends LSX_WETU_Importer, "accommodation" tab)
├── LSX_WETU_Importer_Destination        (extends LSX_WETU_Importer, "destination" tab)
├── LSX_WETU_Importer_Tours              (extends LSX_WETU_Importer, "tour" tab)
├── LSX_WETU_Importer_Settings           (standalone singleton, "settings" tab)
├── LSX_WETU_Importer_Banner_Integration (extends LSX_WETU_Importer, "banners" tab)
├── LSX_WETU_Importer_Post_Columns       (standalone singleton, tour list-table column/search)
└── WETU_Automation                      (standalone singleton, Action Scheduler background sync)
```

`LSX_WETU_Importer` is not abstract, but it functions as a shared base class: the tab classes (`Accommodation`, `Destination`, `Tours`, `Banner_Integration`) extend it directly (`extends LSX_WETU_Importer`) to reuse its API/media/taxonomy/queue helper methods, while `Settings`, `Post_Columns`, and `Welcome` are standalone classes that implement the same conventions (a `display_page()` method, sometimes a singleton pattern) without inheriting.

## Request lifecycle

1. WordPress loads the plugin; `LSX_WETU_Importer::get_instance()` runs in the constructor:
   - Hooks `admin_init`, `init`, `admin_enqueue_scripts`, `admin_menu`.
   - `set_variables()` reads options and determines the current tab (`$_GET['tab']` / AJAX `$_POST['type']`) into `$tab_slug`.
   - Requires the other class files.
   - If not on the default (Welcome) tab, registers the two shared AJAX actions:
     - `wp_ajax_lsx_tour_importer` / `wp_ajax_nopriv_lsx_tour_importer` → search
     - `wp_ajax_lsx_import_items` / `wp_ajax_nopriv_lsx_import_items` → import
2. On `init`, `load_class()` instantiates the correct tab-specific object into `$this->current_importer` based on `$tab_slug`.
3. `register_importer_page()` (on `admin_menu`) registers the plugin with WordPress's built-in importer system (`register_importer()`), so it appears under **Tools → Import**, rendered by `display_page()`.
4. `LSX_WETU_Importer::display_page()` renders the shared page chrome (tab navigation, Wetu connection status, post-status sub-nav) and then delegates to `$this->current_importer->display_page()` for the tab body.
5. Admin JS (search form, import buttons) calls the two AJAX actions, which `LSX_WETU_Importer::process_ajax_search()` / `process_ajax_import()` proxy straight through to `$this->current_importer->process_ajax_search()` / `process_ajax_import()`.

## Import flow (per content type)

Each tab class (`Accommodation`, `Destination`, `Tours`) follows the same shape:

1. **`process_ajax_search()`** — either filters already-imported/queued local posts by status, or performs a live keyword search against the Wetu Search/List API, and renders result rows via `format_row()`.
2. **`process_ajax_import()`** — persists the admin's "content to sync" checkbox selections to a per-type option (e.g. `lsx_wetu_importer_accommodation_settings`), fetches the full record from the Wetu `Get` API, and delegates to `import_row()`.
3. **`import_row()`** — the core mapping routine: creates or updates the WordPress post (`wp_insert_post`/`wp_update_post`), then dispatches to a series of `set_*()` helper methods based on which content types were checked (taxonomies, custom fields, media, related posts).

Tours additionally cascade into creating/linking `accommodation` and `destination` posts per itinerary day (see [class-tours.md](class-tours.md#process_itineraries)), queuing them via `queue_item()` for later sync.

## Cross-cutting concerns (in the base class)

- **Media**: `attach_image()` / `attach_external_image2()` download and sideload Wetu-hosted images into the Media Library, with `get_scaling_url()` building Wetu's `ImageHandler` CDN URL. `find_attachments()` pre-loads existing attachments per post to avoid duplicate downloads.
- **Taxonomies**: `set_term()` / `set_taxonomy()` / `taxonomy_checkboxes()` find-or-create and assign terms.
- **Custom fields**: `save_custom_field()` is the universal post-meta writer; it detects Tour Operator "connection" meta keys and delegates to `save_merged_field()` to append rather than overwrite.
- **Import queue**: `queue_item()` / `save_queue()` / `find_current_accommodation()` implement a simple queue (persisted in the `lsx_wetu_importer_que` option) so that importing a tour automatically queues its related accommodation/destinations for later sync (either manually via the Accommodation/Destination tabs, or automatically via `WETU_Automation::que_sync_action()`).
- **Background sync**: `WETU_Automation` (in `classes/class-wetu-automation.php`) uses [Action Scheduler](https://actionscheduler.org/) to run the same import logic on a schedule (`lsx_wetu_master_sync` daily cron event → `lsx_wetu_sync_tour` / `lsx_wetu_sync_que` / `lsx_wetu_sync_pin` async actions), reusing the interactive importer classes' `import_row()` methods rather than duplicating mapping logic.

## Settings & options reference

| Option name | Written by | Read by | Purpose |
|---|---|---|---|
| `lsx_wetu_importer_settings` | `LSX_WETU_Importer_Settings::save_options()` | `lsx_wetu_get_options()` (helper), all tab classes | Global plugin settings (API key, per-CPT toggles, image sizing, cron schedule). |
| `lsx_wetu_importer_accommodation_settings` | `LSX_WETU_Importer_Accommodation::process_ajax_import()` | `set_variables()` | Last-used "content to sync" checkbox selection for Accommodation. |
| `lsx_wetu_importer_destination_settings` | `LSX_WETU_Importer_Destination::process_ajax_import()` | `set_variables()` | Same, for Destination. |
| `lsx_wetu_importer_tour_settings` | `LSX_WETU_Importer_Tours::process_ajax_import()` | `set_variables()` | Same, for Tours. |
| `lsx_wetu_importer_que` | `save_queue()` / `remove_from_queue()` | `set_variables()`, `WETU_Automation::que_sync_action()` | The cross-type import queue. |
| `lsx_ti_tours` (transient) | `LSX_WETU_Importer::update_options()` | `process_ajax_search()` (Tours), `lsx_wetu_get_post_count()`/`lsx_wetu_get_tour_count()` helpers | 4-hour cache of the Wetu tour/itinerary list. |
| `lsx_ti_tours_api_options` | `update_options()` | `update_options_form()` | Which list filter (Personal/Sample) was last used to populate the transient above. |
| `_lsx-to_settings` (legacy) | Tour Operator plugin | `lsx_wetu_get_options()` (fallback) | Legacy settings location, kept for backward compatibility. |

## Key WordPress hooks

| Hook | Type | Fired/registered by | Purpose |
|---|---|---|---|
| `admin_init` | action | `LSX_WETU_Importer`, `LSX_WETU_Importer_Settings` | Compatibility check; settings-form save handling. |
| `init` | action | `LSX_WETU_Importer`, `WETU_Automation`, `LSX_WETU_Importer_Post_Columns` | Text domain loading, class loading, automation bootstrap. |
| `admin_menu` (priority 200) | action | `LSX_WETU_Importer::register_importer_page()` | Registers the importer under Tools → Import. |
| `admin_enqueue_scripts` (priority 11) | action | `LSX_WETU_Importer::admin_scripts()` | Enqueues admin CSS/JS only on the importer's own screen. |
| `wp_ajax_lsx_tour_importer` / `_nopriv_` | action | `LSX_WETU_Importer` constructor | Search AJAX endpoint. |
| `wp_ajax_lsx_import_items` / `_nopriv_` | action | `LSX_WETU_Importer` constructor | Import AJAX endpoint. |
| `wp_ajax_lsx_import_sync_banners` / `_nopriv_` | action | `LSX_WETU_Importer_Banner_Integration` constructor | Banner re-sync AJAX endpoint. |
| `lsx_wetu_language` | filter | Fired in each `process_ajax_import()` | Lets other code localize/modify the Wetu fetch URL. |
| `lsx_wetu_tour_refresh_url` | filter | `update_options()` | Lets other code modify the tour-list refresh URL. |
| `lsx_wetu_itinerary_complete` | action | `LSX_WETU_Importer_Tours::process_itineraries()` | Fires once a tour's itinerary import completes. |
| `lsx_wetu_importer_search_form` | action | `LSX_WETU_Importer::search_form()` | Lets extensions inject extra search-form fields. |
| `lsx_wetu_importer_settings_before` | action | `LSX_WETU_Importer_Settings::display_page()` | Lets extensions inject extra settings fields. |
| `wetu_automation_tour_flags` / `_destination_flags` / `_accommodation_flags` | filter | `WETU_Automation` | Lets other code customize which fields the cron sync imports. |
| `manage_tour_posts_columns`, `manage_tour_posts_custom_column` | filter/action | `LSX_WETU_Importer_Post_Columns` | Adds the "Ref" column to the Tours list table. |
| `pre_get_posts`, `posts_search` | action/filter | `LSX_WETU_Importer_Post_Columns` | Extends admin search to match `lsx_wetu_ref` meta. |

See [Known Issues](known-issues.md) for a list of bugs/inconsistencies (dead code, dynamic properties, a hardcoded API key, an unauthenticated debug backdoor in `bin/`) discovered while producing this guide.
