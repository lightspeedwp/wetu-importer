# Phase 1 Data Model: WETU Importer WPML Multilingual Import Support

This feature does not introduce new custom database tables. It adds one new piece of postmeta bookkeeping and relies on WPML's own storage for translation relationships. Entities below describe the conceptual model, not new schema beyond what's noted.

## Entities

### WETU Item
External source content identified by a stable ID from the WETU API.

| Field | Description | Source |
|---|---|---|
| `wetu_id` | Stable external identifier for a tour/destination/accommodation/team member | WETU API response |
| `modified_date` | Last-modified timestamp from WETU, used to decide whether to re-pull content | WETU API response |

No changes to this entity's shape; it already exists in the importer's data flow.

### Imported Post
A WordPress post created/updated by the importer for a given WETU Item.

| Field | Description | Storage | Change in this feature |
|---|---|---|---|
| `ID` | WordPress post ID | `wp_posts.ID` | unchanged |
| `post_type` | One of `tour`, `destination`, `accommodation`, `team` | `wp_posts.post_type` | unchanged |
| `lsx_wetu_id` (postmeta) | The WETU Item's `wetu_id` this post was imported from | `wp_postmeta` | **Existing key, semantics extended**: no longer globally unique per site — may now legitimately repeat once per WPML language, each occurrence on a different-language post. All matching logic that reads this key must also filter by language (see Language-Aware Lookup below). |
| `lsx_wetu_modified_date` (postmeta) | Last WETU modified date recorded at import time | `wp_postmeta` | unchanged |

### WPML Translation Group (external, WPML-owned)
WPML's own concept; this feature reads/writes it exclusively through WPML's public API, never directly.

| Field | Description | Owned by |
|---|---|---|
| `trid` | Translation group ID shared by all language-variants of one logical item | WPML core (`icl_translations` table) |
| `element_id` | The WordPress post ID (or term ID) being registered | WPML core |
| `element_type` | `post_{post_type}` or `tax_{taxonomy}` | WPML core |
| `language_code` | The language this specific post/term is in | WPML core |
| `source_language_code` | The language of the "original" post in the group (null for the original itself) | WPML core |

**Relationship**: One WETU Item → one WPML Translation Group (`trid`) → one Imported Post per configured WPML language. Within a translation group, exactly one post is the "source"/original (no `source_language_code`); all others reference it.

## Language-Aware Lookup (behavioral model, not new schema)

Today, `find_current_tours()` / `find_current_accommodation($post_type)` / `get_post_id_by_key_value($wetu_id)` return a single `post_id` per `wetu_id` via a raw SQL join on `lsx_wetu_id`. This feature changes their *effective* contract:

- **Input**: `wetu_id` **and** the language currently being imported into (resolved via WPML's current-language API, defaulting to the site default language when WPML is inactive or the language is unconfigured).
- **Output**: the `post_id` whose `lsx_wetu_id` matches **and** whose WPML-registered language matches the target language — or "not found" if no post exists yet in that language (triggering the insert-then-link-as-translation path), even if a post with the same `wetu_id` already exists in a *different* language.
- **Invariant**: a lookup for language A must never return a post whose WPML language is B, even when both share the same `lsx_wetu_id`.

This is implemented as a language filter added to the existing SQL join (matching on WPML's language table) or, more simply, by taking the existing language-blind result set and cross-checking each candidate's WPML language via `wpml_object_id`/the post's registered language before accepting a match. Either approach satisfies the invariant; the plan does not mandate a specific join strategy, only the outcome.

## State Transitions

A given WETU Item, per WPML-configured language, moves through:

1. **Not imported** → no post exists for this `wetu_id` in this language.
2. **Imported as source** → post created, `lsx_wetu_id` set, registered with WPML as the original of a new (or existing) translation group.
3. **Imported as translation** → post created, `lsx_wetu_id` set, registered with WPML as a translation of the existing translation group (requires a source-language post to already exist for this `wetu_id`).
4. **Re-imported (same language)** → existing post updated in place; WPML translation-group membership unchanged.

No transition allows a post to move to a different language after creation as part of this feature — that remains a manual WPML admin operation, out of scope.
