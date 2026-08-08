# `class-lsx-wetu-importer-post-columns.php`

## Class: `LSX_WETU_Importer_Post_Columns`

**Extends:** none

**Responsibility:** Adds a "Ref" (Wetu reference) admin column to the `tour` post type list table and extends admin search on the `tour` post type so that searching also matches the `lsx_wetu_ref` post meta value, not just title/content. Implemented as a singleton, instantiated by `LSX_WETU_Importer::__construct()` when the "enable tour ref column" setting is on.

### Properties

| Property | Type | Default | Visibility | Static |
|---|---|---|---|---|
| `$instance` | object | none | private | yes — singleton instance holder |
| `$wetu_ref_post_ids` | int[] | `array()` | private | no — post IDs found via wetu_ref meta lookup, passed through to the `posts_search` filter |

### Methods

#### `__construct()`
Registers hooks: `manage_tour_posts_columns` filter → `register_tour_columns()`; `manage_tour_posts_custom_column` action → `output_tour_ref_column()` (priority 10, 2 args); `pre_get_posts` action → `tour_search_by_wetu_ref()`.

#### `get_instance()`
Static singleton accessor; lazily instantiates and caches `self::$instance`.

#### `display_page()`
Empty stub method (no-op body); present likely to satisfy a common interface expected by the importer tab-loading mechanism.

#### `register_tour_columns( $columns )`
Filter callback for `manage_tour_posts_columns`. Rebuilds the columns array so `cb`, `title`, and a new `wetu_ref` column (labelled "Ref") appear first, followed by the remaining original columns.
- Params: `$columns` (array). Returns: array.

#### `output_tour_ref_column( $column, $post_id )`
Action callback for `manage_tour_posts_custom_column`. When `$column === 'wetu_ref'`, echoes the post's `lsx_wetu_ref` meta value (escaped).

#### `register_sortable_columns( $columns = array() )`
Adds `wetu_ref` (mapped to sort key `price_per_month`) to a sortable-columns array.
- **Note:** not wired up to any hook in `__construct()`, so it currently appears unused/dead code — see [Known Issues](known-issues.md).

#### `tour_search_by_wetu_ref( $query )`
Action callback for `pre_get_posts`. Only runs in admin, on the main query, for `post_type=tour` with a non-empty search term (`s`). Runs a direct `$wpdb` query against `postmeta` for `lsx_wetu_ref LIKE %search%` and, if matches are found, stores the matching post IDs in `$wetu_ref_post_ids` and hooks `posts_search` (→ `tour_wetu_ref_posts_search()`).
- **Side effects:** Direct/uncached DB read (phpcs-ignored for direct-query/no-caching rules, since it's a narrow one-off search); conditionally registers the `posts_search` filter.

#### `tour_wetu_ref_posts_search( $search, $_query )`
Filter callback for `posts_search`. Removes itself immediately (one-shot filter) to avoid affecting subsequent queries, then rewrites the `$search` SQL clause so the existing title/content `AND (...)` condition is extended with `OR {$wpdb->posts}.ID IN (...)` using the previously matched `$wetu_ref_post_ids`, keeping it inside the same AND block so outer post_type/status conditions remain intact. Clears `$wetu_ref_post_ids` afterward.
- Params: `$search` (string), `$_query` (`WP_Query`). Returns: string.
- **Side effects:** Mutates the raw SQL search fragment used by `WP_Query`; removes its own filter hook.
