# `class-lsx-wetu-importer-welcome.php`

## Class: `LSX_WETU_Importer_Welcome`

**Extends:** none

**Responsibility:** Renders the plugin's "Welcome" onboarding screen inside wp-admin (the default tab), showing a 4-step wizard (Import tours → Import accommodation → Import destinations → Done) with links and live post counts. Implemented as a singleton. Purely presentational — no data mutation.

### Properties

| Property | Type | Default | Visibility | Static |
|---|---|---|---|---|
| `$instance` | object | none | private | yes — singleton instance holder |

### Methods

#### `__construct()`
Empty body; no hooks registered.

#### `get_instance()`
Static singleton accessor; lazily creates and returns `self::$instance`.

#### `display_page()`
Outputs the welcome heading/intro text, then calls `importer_steps()` and `welcome_blocks()`.

#### `importer_steps()`
Renders the numbered 4-step progress header (Tours / Accommodation / Destination / Done) with links into `admin.php?import=lsx-wetu-importer&tab=<tab>`.

#### `welcome_blocks()`
Renders the container row that calls `tour_block()`, `accommodation_block()`, `destination_block()`, and `end_block()` each inside its own `.welcome-block postbox` div.

#### `tour_block()`
Outputs the "Importing tours" card: description text, a "Sync tours" button link to the tour tab, and a list of Published/Pending/Draft counts using `lsx_wetu_get_post_count( 'tour', <status> )` (see [helpers-and-bootstrap.md](helpers-and-bootstrap.md)).

#### `accommodation_block()`
Outputs the "Import and publish accommodation" card, similarly listing Published/Pending/Draft counts plus a "Wetu Queue" count via `lsx_wetu_get_queue_count( 'accommodation' )`.

#### `destination_block()`
Same pattern as `accommodation_block()` but for the `destination` post type.

#### `end_block()`
Outputs the final "Done" card with static congratulatory copy.

None of these render methods take/return data other than echoing HTML directly.
