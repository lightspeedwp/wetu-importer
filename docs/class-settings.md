# `class-lsx-wetu-importer-settings.php`

## Class: `LSX_WETU_Importer_Settings`

**Extends:** none (standalone class; implements its own singleton pattern rather than inheriting from `LSX_WETU_Importer`).

**Overall responsibility:** Implements the plugin's global Settings admin screen — a single page rendering grouped `form-table` sections (API key; Tours; Accommodation; Destinations; Images; Sync/Cron) that control every configurable toggle consumed by the other importer classes. It also owns saving these settings into the `lsx_wetu_importer_settings` WordPress option, and is instantiated as a singleton via `get_instance()`.

### Properties

| Property | Type / Default | Visibility | Description |
|---|---|---|---|
| `$instance` | `object`, unset by default | `private static` | Holds the singleton instance of the class, lazily created by `get_instance()`. |
| `$defaults` | `array`, populated in constructor | `public` | The full set of default values for every settings field. See key list below. |
| `$fields` | `array`, `array_keys( $this->defaults )` | `public` | Convenience list of all valid settings field keys, derived from `$defaults`; used implicitly by `save_options()` to know which POST keys to persist. |

**`$defaults` keys** (all default to `''` empty string except where noted): `api_key`, `disable_tour_title`, `disable_tour_descriptions`, `disable_tour_tags`, `enable_tour_featured_random`, `disable_accommodation_title`, `disable_accommodation_descriptions`, `disable_accommodation_filtering`, `disable_accommodation_excerpts`, `disable_destination_title`, `disable_destination_descriptions`, `disable_destination_image_featured`, `disable_destination_image_banner`, `image_replacing` (default `'on'`), `image_limit` (default `'12'`), `image_scaling` (default `'on'`), `width` (default `'1200'`), `height` (default `'800'`), `scaling` (default `'raw'`), `enable_tour_ref_column`, `cron_schedule` (default `'daily'`), `accommodation_images_cron`, `accommodation_images_cron_featured`.

### Methods

#### `__construct()`
Initializes the `$defaults` array with all settings keys and their default values, derives `$fields` from it, and hooks the option-saving routine into WordPress's admin request lifecycle.
- **Hooks:** Registers action `admin_init` → `save_options()` (so any POST of the settings form is processed on every admin page load, not just the settings screen itself).

#### `get_instance()`
Standard singleton accessor — lazily instantiates `LSX_WETU_Importer_Settings` on first call and returns the shared instance thereafter.

#### `display_page()`
Renders the full Settings admin page markup. Loads current settings via the `lsx_wetu_get_options()` global helper function, merges them over `$this->defaults` with `wp_parse_args()`, and outputs a single form (protected by a nonce, `lsx_wetu_importer_save`/`lsx_wetu_importer_save_options`) with six grouped sections:
- **General**: API Key text field.
- **Tours**: Enable Custom Titles, Disable Descriptions, Disable Tags/Travel Styles, Enable Reference Column, Randomize Featured Image (checkboxes).
- **Accommodation**: Enable Custom Titles, Disable Descriptions, Disable Description Filtering, Disable Excerpts (checkboxes).
- **Destinations**: Enable Custom Titles, Disable Descriptions, Disable Featured Image, Disable Banner Image (checkboxes).
- **Images**: Replace Images (checkbox), Image Limit (text), Enable Image Scaling (checkbox), Width/Height (text), Scaling mode (radio group: `raw`, `c`, `h`, `w`, `nf`, `n`, `W`).
- **Sync**: Cron Schedule (select: daily or weekly-by-day-of-week), Accommodation Images cron toggle, Featured Images cron toggle.
- **Hooks:** Fires action `lsx_wetu_importer_settings_before` immediately after the nonce field, allowing other code/extensions to inject additional settings markup before the "General" section.
- **Note:** The `foreach ( $options as $key => $value ) { $value = trim( $value ); }` loop is a no-op — see [Known Issues](known-issues.md).

#### `save_options()`
Handles submission of the settings form. Validates the nonce, then iterates over every key in `$this->defaults` and, for each, sanitizes the corresponding `$_POST` value (defaulting to an empty string for unchecked checkboxes/missing fields) before saving the complete settings array.
- Executed on `admin_init` (registered in the constructor).
- **Side effects:**
  - Verifies nonce via `wp_verify_nonce( $_POST['lsx_wetu_importer_save_options'], 'lsx_wetu_importer_save' )`; exits early if missing/invalid.
  - Sanitizes each field with `sanitize_text_field()`.
  - `update_option( 'lsx_wetu_importer_settings', $data_to_save )` — persists the entire settings array as a single WordPress option (full overwrite each save, ensuring unchecked checkboxes are correctly cleared to `''`).
