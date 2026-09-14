# `class-lsx-wetu-importer-banner-integration.php`

## Class: `LSX_WETU_Importer_Banner_Integration`

**Extends:** `LSX_WETU_Importer` (see [class-lsx-wetu-importer.md](class-lsx-wetu-importer.md))

**Responsibility:** Provides an admin sub-screen ("Banners" tab) that lists accommodation posts which have banner images stored in their `image_group` meta, lets an admin trigger an AJAX re-download of those images from Wetu at full banner resolution (1920x800), and re-attaches them as media.

### Properties

| Property | Type | Default | Visibility | Description |
|---|---|---|---|---|
| `$tab_slug` | string | `'banners'` | public | The slug/post-type identifier used to build this importer tab's URL and query args. |

### Methods

#### `__construct()`

Registers `wp_ajax_lsx_import_sync_banners` and `wp_ajax_nopriv_lsx_import_sync_banners` actions pointing to `sync_new_banner()`.
- **Side effects:** Registers WordPress AJAX action hooks only.

#### `display_page()`

Renders the "Banners" admin screen: queries `accommodation` posts (published/pending/draft/future/private) that have both a `lsx_wetu_id` and a non-empty `image_group.banner_image` meta value, and displays a table of each accommodation's current banner image thumbnails with their pixel dimensions.
- **Side effects:** Runs a `WP_Query` (up to 1000 posts) with a meta_query; outputs HTML directly.

#### `sync_new_banner()`

AJAX handler (bound to both `wp_ajax_lsx_import_sync_banners` and `wp_ajax_nopriv_lsx_import_sync_banners`). Given a `post_id` via `$_POST`, it reads that post's `image_group` and `lsx_wetu_id` meta, loops through each stored `banner_image`, re-downloads it via `format_wetu_url()` + `attach_external_image2()`, and rebuilds the `image_group['banner_image']` meta array with the freshly attached media IDs.
- **Security gap:** Only verifies via `check_ajax_referer( 'lsx_wetu_ajax_action', 'security' )` — a valid nonce, not a capability check. There is no `current_user_can()` check, and the supplied `post_id` is used without verifying its post type or ownership. Combined with the `nopriv` registration, this endpoint accepts requests from unauthenticated visitors and will read/write meta and create attachments for any post ID they supply. See [Known Issues](known-issues.md).
- **Side effects:** Deletes and re-adds the `image_group` post meta; performs external HTTP downloads and creates new media attachments; echoes `true`/`false` as the AJAX response; terminates with `die()`.

#### `format_wetu_url( $post_id )`

Builds the Wetu image-handler URL for a given attachment (`$post_id`), sized to `c1920x800`, using the stored `$this->wetu_id` and a filename produced by `format_filename()`.
- No side effects (pure string builder); relies on `$this->wetu_id` set previously in `sync_new_banner()`.

#### `format_filename( $post_id )`

Derives a URL-encoded filename (with correct extension) from an attachment's title and mime type, for use in the Wetu image URL. Returns `false` for unsupported mime types.

#### `attach_external_image2( $url = null, $post_data = array(), $post_id = '' )`

Downloads an external image via `wp_remote_get()`, writes it to a temp file using `WP_Filesystem`, and sideloads it into the media library as an attachment tied to `$post_id` via `media_handle_sideload()`.
- **Side effects:** File I/O (creates a temp file via `tempnam( '/tmp', 'FOO' )`, writes/chmods it via `$wp_filesystem`), performs an HTTP GET, inserts a new attachment post, deletes the temp file on failure. Returns the attachment ID, `false`, or a `WP_Error` if no URL supplied.
- **Note:** This class extends `LSX_WETU_Importer`, so this method overrides the inherited `attach_external_image2()` rather than avoiding a name collision. Its parameter order differs from the parent's version (see [Known Issues](known-issues.md)), which is a documented API compatibility risk for any code calling it polymorphically through a `LSX_WETU_Importer` reference.
