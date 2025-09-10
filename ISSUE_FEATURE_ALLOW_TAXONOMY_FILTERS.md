## Feature Request: Allow 3rd Party Plugins to Filter Taxonomies Synced by Wetu Importer

### Description
Add new filters to the Wetu Importer plugin to allow 3rd party plugins to filter the taxonomies that are synced during import. This will enable developers to customize which taxonomies are imported or excluded, improving extensibility and compatibility with other plugins.

### Requirements
- Introduce WordPress `apply_filters()` hooks in the taxonomy sync logic.
- Document the available filters and expected arguments in the code and readme.
- Ensure backward compatibility for existing sync behavior.

### Example Usage
```php
add_filter( 'lsx_wetu_importer_sync_taxonomies', function( $taxonomies, $post_type ) {
    // Modify $taxonomies array as needed
    return $taxonomies;
}, 10, 2 );
```

### Acceptance Criteria
- Filters are available for 3rd party plugins to modify synced taxonomies.
- Documentation is updated to describe the new filters and usage examples.
- Existing functionality is not broken for current users.

### Additional Context
This feature will make the Wetu Importer more flexible for custom integrations and advanced workflows.

---

Closes #feature/allow-taxonomy-filters