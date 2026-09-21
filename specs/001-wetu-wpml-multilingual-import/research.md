# Phase 0 Research: WETU Importer WPML Multilingual Import Support

All unknowns below were resolved via direct codebase inspection (`wetu-importer` v1.5.2 and the locally installed `sitepress-multilingual-cms` v5.0.1) rather than external research, since both systems are present in the local dev environment. No `NEEDS CLARIFICATION` markers remain in the plan's Technical Context.

## 1. How does WETU Importer decide create-vs-update today?

**Decision**: Treat the existing `lsx_wetu_id` postmeta + `find_current_*()` SQL lookups as the mechanism to extend, not replace.

**Rationale**: Every importer class matches an existing post purely by the `lsx_wetu_id` postmeta value:
- `find_current_tours()` (`class-lsx-wetu-importer-tours.php`) and `find_current_accommodation( $post_type )` (`class-lsx-wetu-importer.php`, also used for `destination`) run a raw SQL join on `postmeta.meta_key = 'lsx_wetu_id'` filtered by `post_type`, returning a `[wetu_id => post_id]` map.
- The admin JS populates a search-results table from this map; the actual `$_POST['post_id']` sent to `process_ajax_import()` is what `import_row()` uses to decide `wp_update_post()` (existing) vs `wp_insert_post()` (new) — `add_post_meta($id, 'lsx_wetu_id', $wetu_id)` runs only on the insert branch.
- This lookup has **no language dimension**. Once the same `wetu_id` can exist as two separate posts (one per language), the current SQL would either return only one of them (a PHP array can only have one value per `wetu_id` key) or, if used to pre-select `post_id` for a re-import in a different language, it would associate the wrong post.

**Alternatives considered**:
- *Rewrite matching using WPML's `trid`/translation APIs exclusively, discarding `lsx_wetu_id`.* Rejected: `lsx_wetu_id` is the external system's identity key (WETU's id space), independent of WPML; it must stay as the primary "is this the same WETU item" signal. WPML's `trid` is the *cross-language* linking mechanism layered on top, not a replacement for external-ID matching.
- *Store the WPML language code as a second postmeta key we invent ourselves and query on.* Rejected in favor of using `wpml_object_id`/`icl_object_id`, WPML's own supported lookup, so WPML remains the single source of truth for what language a post is in — avoids drift between our own bookkeeping and WPML's actual state.

## 2. Which WPML API surface to build against

**Decision**: Use WPML's officially documented public compatibility hooks/functions, guarded by `defined('ICL_SITEPRESS_VERSION')`, bootstrapped on the `wpml_loaded` action.

**Rationale**: Confirmed present and stable in the installed `sitepress-multilingual-cms` v5.0.1 core plugin (`SitePress::api_hooks()` in `sitepress.class.php`):
- **Set a post's language / link it to a translation group**: fire `do_action('wpml_set_element_language_details', [ 'element_id' => $post_id, 'element_type' => 'post_' . $post_type, 'trid' => $trid_or_null, 'language_code' => $lang, 'source_language_code' => $source_lang_or_null ])`.
- **Resolve the translated ID of an existing post** (to find/set `$trid` and to resolve cross-references such as a tour's linked destination in the target language): `apply_filters('wpml_object_id', $element_id, $element_type, true, $lang)` (equivalently the convenience wrapper `icl_object_id()`).
- **Detect WPML is active**: `defined('ICL_SITEPRESS_VERSION')`.
- **Get current/default/active languages**: `apply_filters('wpml_current_language', '')`, `apply_filters('wpml_default_language', '')`, `apply_filters('wpml_active_languages', [])`.
- **Bootstrap timing**: hook integration setup onto `add_action('wpml_loaded', ...)` rather than assuming plugin-load order against `plugins_loaded`.
- **Declare translatable post types/taxonomies/custom fields with zero admin configuration**: ship a `wpml-config.xml` at the plugin root (WPML's standard, documented mechanism — parsed by `classes/xml-config/class-wpml-config.php` in WPML core). This avoids requiring every site admin to manually configure the Post Type/Taxonomy Translation screens.

**Alternatives considered**:
- *Legacy `wpml_add_translatable_content()` / `wpml_update_translatable_content()` wrapper functions* (`inc/wpml-api.php`). Rejected as the primary mechanism: this legacy API explicitly rejects core content types via `_wpml_api_allowed_content_type()` and is really a thin wrapper around the same `set_element_language_details()` call; using the `wpml_set_element_language_details` action directly is the more current, more flexible, and equally-documented path and works uniformly for all our custom post types.
- *WPML String Translation (`icl_register_string()` / `wpml_register_single_string`) for custom field values.* Rejected as a hard dependency: that hook is implemented in WPML's separate **String Translation** add-on plugin, not in WPML core (`sitepress-multilingual-cms`), and is not confirmed installed on target sites. Any use of it must be guarded with `function_exists('icl_register_string')` and must no-op safely if absent. The `wpml-config.xml` `<custom-fields>` declaration is the preferred mechanism for translating custom-field values tied to a post, since it only depends on WPML core.
- *Direct manipulation of WPML's `icl_translations` database table.* Rejected: unsupported, undocumented, and fragile across WPML versions; violates the assumption (spec) that integration goes through WPML's supported public API only.

## 3. Where to put the WPML integration code

**Decision**: A single new helper class (`class-wpml-compat.php`) encapsulating all WPML API calls, called from each of the three importer classes' `import_row()` methods and from the shared `team`-creation path in the base class.

**Rationale**: `wetu-importer` has no formal abstract base — `LSX_WETU_Importer_Tours`, `_Accommodation`, and `_Destination` each `extends LSX_WETU_Importer` directly and each has its own `import_row()`/`find_current_*()` implementation (near-duplicated, not shared via a template method). Rather than restructuring that inheritance (out of scope for a compat fix, and risks its own regressions), a dedicated helper class keeps all new WPML-specific logic in one testable, guard-able place, called identically from each importer class. `class-wetu-automation.php` (background sync) calls the same `import_row()` methods, so it gets the behavior for free without separate changes.

**Alternatives considered**:
- *Duplicate the WPML calls inline in each importer class.* Rejected: four near-identical copies of language-detection/registration logic, higher regression risk when WPML's API evolves, harder to unit test in isolation.
- *Refactor the importer classes onto a true shared abstract base class first.* Rejected as out of scope: valuable but unrelated cleanup that would expand this compat fix's blast radius and risk beyond what LS-4209 asks for.

## 4. Testing approach given WPML is not a composer dependency

**Decision**: Add PHPUnit coverage for the new `class-wpml-compat.php` helper using lightweight function/constant stubs (define `ICL_SITEPRESS_VERSION` and stub `wpml_object_id`/`wpml_set_element_language_details` hook handlers in the test bootstrap) rather than requiring the real WPML plugin as a test dependency; explicitly include a test run with none of those defined/stubbed to prove the "WPML absent → unchanged behavior" path (FR-001, FR-011).

**Rationale**: `composer.json` has no WPML dependency today (dev deps only include `tour-operator` and the `lsx` theme for integration context), and WPML is a commercial plugin not installable via public Packagist/WPackagist in CI. Stubbing the specific handful of hooks/functions this feature calls is sufficient to test our own logic; it does not need to validate WPML's internals.

**Alternatives considered**:
- *Skip automated tests for WPML paths, rely on manual QA only.* Rejected: the existing `tests/` setup already has PHPUnit wired up, and the Linear issue's Definition of Done expects "compatible behavior confirmed" — automated coverage of the helper class is feasible and valuable even without the real plugin.
- *Vendor a copy of WPML core into the test environment.* Rejected: license/distribution concerns for a commercial plugin, and unnecessary — the integration surface is a small, stable set of hooks that stub cleanly.

## Summary of resolved unknowns

| Technical Context field | Resolution |
|---|---|
| Language/Version | PHP 8.0+ (existing plugin baseline) |
| Primary Dependencies | WordPress, Tour Operator (required), Tour Operator Team (optional), WPML core v5.0.1-compatible (optional) |
| Testing | PHPUnit with stubbed WPML hooks/constants; explicit WPML-absent test case |
| Target Platform | WordPress plugin, existing AJAX + Action Scheduler background sync |
| Project Type | Single WordPress plugin, no new project/module boundaries |
