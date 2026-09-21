# Contract: WETU Importer ↔ WPML Integration

This is the external interface this feature introduces: the set of WPML public API calls the importer will make, and the plugin-level declaration file it will ship. This is the "contract" surface for this feature — there is no new REST/AJAX endpoint or public PHP API of our own; the contract is entirely against WPML's existing, documented compatibility API (WPML core `sitepress-multilingual-cms`, tested against v5.0.1).

## 1. Detection & bootstrap

```php
// Detect WPML core is active
defined( 'ICL_SITEPRESS_VERSION' )

// Bootstrap integration once WPML is fully loaded (not plugins_loaded)
add_action( 'wpml_loaded', function () {
    // register our wpml-config.xml-declared behavior is automatic;
    // any runtime hook registration for the importer happens here
} );
```

**Guarantee**: All WPML-specific code paths are skipped entirely when `ICL_SITEPRESS_VERSION` is undefined. No behavior change on non-WPML sites (FR-001, FR-011).

## 2. Declaring translatable content (`wpml-config.xml`, plugin root)

```xml
<wpml-config>
    <custom-types>
        <custom-type translate="1">tour</custom-type>
        <custom-type translate="1">destination</custom-type>
        <custom-type translate="1">accommodation</custom-type>
        <custom-type translate="1">team</custom-type>
    </custom-types>
    <taxonomies>
        <!-- populated per the actual taxonomies registered by Tour Operator / Tour Operator Team -->
    </taxonomies>
    <custom-fields>
        <!-- lsx_wetu_id and lsx_wetu_modified_date are NOT translatable content and are
             intentionally omitted; only genuinely user-facing translatable custom fields
             (if any exist on these post types) would be listed here -->
    </custom-fields>
</wpml-config>
```

**Guarantee**: Sites installing WPML alongside this plugin do not need to manually visit WPML's Post Type/Taxonomy Translation admin screens to make these four post types translatable.

## 3. Registering language / linking a translation (called from each `import_row()`)

```php
/**
 * @param int         $post_id            The just-created/updated WordPress post ID.
 * @param string      $post_type          'tour' | 'destination' | 'accommodation' | 'team'.
 * @param string      $language_code      Target language for this import run.
 * @param int|null    $trid               Existing translation group ID to link into, or null for a new group.
 * @param string|null $source_language    The source/original language code, or null if this post IS the source.
 */
do_action( 'wpml_set_element_language_details', array(
    'element_id'           => $post_id,
    'element_type'         => 'post_' . $post_type,
    'trid'                 => $trid,
    'language_code'        => $language_code,
    'source_language_code' => $source_language,
) );
```

**Preconditions**: `$post_id` already exists (post inserted/updated). `$trid` is resolved beforehand (see §4) by looking up whether a post for this `wetu_id` already exists in another language.

**Postconditions**: The post is a member of the WPML translation group `$trid`, in language `$language_code`. If it was the first post in the group, WPML assigns and returns a new `$trid` internally (available via `apply_filters('wpml_element_trid', ...)` if needed for subsequent linking within the same import batch).

## 4. Resolving translations / language-aware lookups (called before insert/update decision)

```php
// Get the translated post ID of $source_post_id in $language_code, or false if none exists.
$translated_id = apply_filters( 'wpml_object_id', $source_post_id, 'post_' . $post_type, false, $language_code );

// Equivalent convenience wrapper:
$translated_id = icl_object_id( $source_post_id, $post_type, false, $language_code );
```

**Contract for the importer's language-aware existing-post lookup**: given a `wetu_id` and a target `language_code`, the importer must resolve at most one matching `post_id` — the post in that specific language — never a post belonging to a different language's translation. See [data-model.md](./../data-model.md#language-aware-lookup-behavioral-model-not-new-schema) for the full behavioral contract.

## 5. Reading current/active languages

```php
$current_language  = apply_filters( 'wpml_current_language', '' );
$default_language  = apply_filters( 'wpml_default_language', '' );
$active_languages  = apply_filters( 'wpml_active_languages', array() );
```

**Guarantee**: If the resolved `$current_language` is not a key present in `$active_languages`, the importer falls back to `$default_language` and surfaces a message to the user (FR-009), rather than registering content under an unconfigured language code.

## 6. Custom field translation (optional, best-effort)

```php
if ( function_exists( 'icl_register_string' ) ) {
    icl_register_string( 'wetu-importer', $field_name, $field_value );
}
```

**Guarantee**: This call is only made when the function exists (i.e. the separate WPML String Translation add-on is active). Its absence must never cause an error or block the import — the `wpml-config.xml` `<custom-fields>` declaration is the primary mechanism (§2); this is a secondary, best-effort enhancement only.

## Out of scope for this contract

- Any hook/API related to TO Reviews or TO Specials post types (no existing import integration to extend — see spec.md scope note).
- Direct reads/writes to WPML's database tables (`icl_translations` etc.) — always go through the filters/actions above.
- Configuring WPML itself (languages, default language) on the target site — assumed already done (spec.md Assumptions).
