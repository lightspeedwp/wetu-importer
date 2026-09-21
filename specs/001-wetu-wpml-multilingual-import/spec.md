# Feature Specification: WETU Importer WPML Multilingual Import Support

**Feature Branch**: `feature/ls-4209-compat-wetu-importer-support-wpml-multilingual-imports`

**Created**: 2026-09-17

**Status**: Draft

**Input**: User description: "compat: WETU Importer - support WPML multilingual imports (LS-4209). Integrate WPML with the WETU Importer (https://github.com/lightspeedwp/wetu-importer) so Tour Operator Suite can import content in multiple languages. Affected extensions: Tour Operator, TO Reviews, TO Specials, TO Team. Expected behavior: The WETU Importer supports importing content in multiple languages through WPML. Acceptance criteria from Linear issue LS-4209: issue reproducible and documented, compatible behavior confirmed on affected platforms, no adverse impact on other platforms, documentation/changelog updated if needed, PR uses correct branch prefix (compat/), PR description updated with relevant details, changelog entry prepared, labels/types match org standards."

**Linear Issue**: [LS-4209](https://linear.app/lightspeedwp/issue/LS-4209/compat-wetu-importer-support-wpml-multilingual-imports)

**Scope note**: The Linear issue lists Tour Operator, TO Reviews, TO Specials, and TO Team as affected extensions. Codebase review of `wetu-importer` (v1.5.2) found it currently only creates/updates `tour`, `destination`, and `accommodation` posts (Tour Operator core), plus `team` posts when the Tour Operator Team extension is active (`post_type_exists('team')` guard). There is no code path anywhere in `wetu-importer` that creates content for TO Reviews or TO Specials — those extensions have no existing import integration to make WPML-compatible. This spec is scoped to the post types the importer actually creates today; TO Reviews and TO Specials are explicitly out of scope (see Assumptions).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Import WETU content into a specific site language (Priority: P1)

A tour operator content editor running WPML runs the WETU Importer while the site's current admin language (set via WPML's own admin language switcher) is a secondary language (e.g. German, on a site whose default language is English). The imported tour, destination, and accommodation content is created as a translation correctly linked to the existing default-language content, rather than as a duplicate, untranslated, or orphaned post.

**Why this priority**: This is the core compatibility gap described in the issue — without it, WPML sites cannot use the importer at all without creating duplicate or disconnected content, which is the primary blocker for Tour Operator Suite customers who run multilingual sites.

**Independent Test**: Can be fully tested by configuring a WPML multilingual site with two languages, running the WETU Importer once per language for the same WETU item, and confirming both results appear as linked translations of one WPML translation group in the WordPress admin, with no duplicate default-language post created on the second run.

**Acceptance Scenarios**:

1. **Given** a WPML site with English as the default language and German as an active secondary language, **When** an editor imports a WETU tour while in the English admin language, **Then** the tour is created as the original ("source") post and registered with WPML in the site's default language.
2. **Given** the same WETU tour has already been imported in English, **When** an editor switches the admin language filter to German and re-runs the import for that same WETU item, **Then** the importer creates a German translation linked to the existing English post via WPML's translation relationships, instead of creating a second, unlinked English post.
3. **Given** a WETU import that includes taxonomy terms (e.g. destinations, activity types) and custom fields, **When** the import runs in a secondary language, **Then** taxonomy terms and translatable custom field values resolve to their existing translated equivalents where available, and register as translatable when no translation exists yet.
4. **Given** a tour import cascades into creating linked `destination` and/or `accommodation` posts (as it does today for itinerary items not yet imported), **When** the import runs in a secondary language, **Then** those cascade-created posts also receive correct WPML language/translation-group registration consistent with the parent tour, not just the top-level post being imported.

---

### User Story 2 - Import Team content correctly registered with WPML (Priority: P2)

A site running the Tour Operator Team extension imports content via the WETU Importer, and the resulting `team` posts are correctly registered with WPML the same way core Tour Operator content (`tour`, `destination`, `accommodation`) is.

**Why this priority**: Team is the one extension beyond core Tour Operator that this importer already creates content for (guarded by `post_type_exists('team')`); without this, WPML support would be incomplete even if the core Tour Operator import path works.

**Independent Test**: Can be tested independently by activating the Tour Operator Team extension alongside WPML and the WETU Importer, importing WETU content that includes team members, and confirming the resulting `team` post type is correctly linked into WPML's translation system.

**Acceptance Scenarios**:

1. **Given** the Tour Operator Team extension is active alongside WPML, **When** WETU content that includes team members is imported in a secondary language, **Then** each `team` post is linked as a WPML translation of the corresponding source-language `team` post (or created as a new source post if none exists).
2. **Given** the Tour Operator Team extension is NOT active, **When** an import runs that would otherwise create `team` posts, **Then** behavior is unchanged from today (team creation is already skipped) and no WPML-related errors occur.

---

### User Story 3 - Re-run imports without breaking existing translations (Priority: P3)

An editor re-runs the WETU Importer (e.g. a scheduled sync via the existing Action Scheduler-based background sync, or a manual refresh to pick up updated WETU data) on content that has already been imported and translated. Existing WPML translation links and any manually edited translated content are preserved rather than being overwritten or disconnected.

**Why this priority**: Import is a recurring operation (manual re-import, and an existing background/cron sync). If re-imports break translation links or clobber human-edited translations, the feature would be unsafe to use in ongoing operation even if the first import works.

**Independent Test**: Can be tested by importing an item, manually editing the translated post, re-running the import for the same WETU item and language, and confirming the translation link remains intact and editor-made changes are handled per the defined update behavior (documented, not silently discarded without warning).

**Acceptance Scenarios**:

1. **Given** a WETU item has already been imported and translated, **When** the same item is re-imported in the same language, **Then** the existing translated post is updated in place and remains linked to the same WPML translation group (no duplicate post is created).
2. **Given** an editor has manually modified a translated post, **When** a re-import updates that same post from WETU source data, **Then** the behavior (update vs. skip vs. flag for review) is consistent and documented so editors know what to expect.
3. **Given** the same WETU ID has been imported into two different languages (creating two separate WordPress posts), **When** either language's copy is re-imported, **Then** the importer's existing-post lookup correctly matches only the post in the current language, and never conflates or overwrites the other language's post.

### Edge Cases

- What happens when WPML is installed but not active, or is active without any secondary languages configured? Import should behave as it does today (single-language), with no errors.
- What happens when the importer runs in a language that WPML is not configured to support? The importer should fall back to the site default language and surface a clear message rather than failing silently or creating misconfigured content.
- How does the system keep translation relationships consistent across a tour and its cascade-created `destination`/`accommodation` posts during a multilingual import?
- What happens when a taxonomy term or custom field required for translation does not yet have a WPML translation available — is the original-language value used as a fallback, and is that fallback clearly identifiable to editors?
- What happens if WPML's translation tables are in an inconsistent state (e.g. orphaned translation group) at the time of import — does the importer error out clearly instead of compounding the inconsistency?
- **Existing-post matching currently has no language dimension**: today, the importer matches an existing post purely by its stored WETU ID (`lsx_wetu_id` postmeta), independent of language. Once the same WETU ID can legitimately exist as separate posts in multiple languages, what happens if the language-blind lookup is used unmodified — could it match/update the wrong language's post? This must be resolved, not left as an open risk.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The WETU Importer MUST detect whether WPML is active and adapt its import behavior accordingly, with no change to current single-language behavior when WPML is inactive.
- **FR-002**: When WPML is active, the importer MUST register each newly imported post with WPML in the language currently selected via WPML's admin language switcher at the time of import.
- **FR-003**: When a WETU item is imported in a language other than its original import language, the importer MUST link the new post as a WPML translation of the existing source-language post (same translation group), rather than creating an unlinked duplicate.
- **FR-004**: The importer MUST apply the translation-linking behavior described in FR-002 and FR-003 to every post type it currently creates: `tour`, `destination`, `accommodation`, and `team` (when the Tour Operator Team extension is active), including posts of these types that are cascade-created during a tour import (e.g. destination/accommodation stubs referenced by an itinerary).
- **FR-005**: The importer's existing-post matching logic MUST become language-aware: given a WETU ID, it MUST match only the post that exists in the language currently being imported, and MUST NOT match or update a post belonging to a different language's translation.
- **FR-006**: The importer MUST resolve translatable taxonomy terms and custom fields to their existing translated equivalents when available, and MUST register untranslated taxonomy terms so they can be translated via WPML's supported mechanisms, rather than being silently duplicated per language.
- **FR-007**: Re-running an import for a WETU item/language pair that has already been imported MUST update the existing linked post rather than creating a new, disconnected post.
- **FR-008**: The importer MUST NOT alter or break existing WPML translation relationships for content that was previously imported or manually translated.
- **FR-009**: When WPML is active but the current admin/import language is not one of the site's configured WPML languages, the importer MUST fall back to the site default language and present a clear message to the user explaining the fallback.
- **FR-010**: Compatibility behavior MUST be documented (in the WETU Importer repository's changelog and/or `docs/` folder) so support staff and integrators understand how multilingual import works, including that TO Reviews and TO Specials are not import-integrated and therefore not covered.
- **FR-011**: The feature MUST NOT introduce regressions to WETU import behavior on sites that do not run WPML (verified via acceptance criteria "no adverse impact on other platforms").
- **FR-012**: The feature MUST NOT introduce regressions to the existing background/scheduled sync path (Action Scheduler-based automation) that reuses the same import logic as manual imports.

### Key Entities

- **WETU Item**: External source content (tour, destination, accommodation, team member) identified by a stable WETU ID (`lsx_wetu_id` postmeta), imported into WordPress as one or more posts.
- **WPML Translation Group**: The set of posts across languages that WPML considers to be translations of one another, linked via a shared translation group identifier (`trid`).
- **Affected Post Type**: A WordPress post type the importer currently creates or updates: `tour`, `destination`, `accommodation` (all core Tour Operator), and `team` (Tour Operator Team extension, when active).
- **Translatable Term/Field**: A taxonomy term or custom field value on an affected post type that WPML is configured to make translatable.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: On a WPML-enabled site, importing the same WETU item once per configured language results in exactly one post per language, all correctly linked as translations of one another, with zero unlinked duplicate posts.
- **SC-002**: All post types the importer creates today (`tour`, `destination`, `accommodation`, `team`) exhibit the same correct translation-linking behavior when tested individually, including when cascade-created during a tour import.
- **SC-003**: Re-running an import on previously imported and translated content preserves 100% of existing WPML translation links across at least 3 consecutive re-import cycles in testing, with zero cross-language mismatches.
- **SC-004**: Sites without WPML installed or active show no behavioral difference in import results before and after this change (zero regressions in non-WPML acceptance testing).
- **SC-005**: Support/documentation updates are published such that a new integrator can configure a multilingual WETU import without needing to contact engineering for clarification, including a clear statement of what is and isn't covered (Reviews/Specials excluded).

## Assumptions

- WPML (WordPress Multilingual Plugin — "WPML Multilingual CMS" core plugin, tested against v5.0.1), including its core translation-management APIs, is the only multilingual plugin in scope; other multilingual solutions (e.g. Polylang) are out of scope for this feature.
- **TO Reviews and TO Specials are out of scope.** Neither extension has any existing import integration in `wetu-importer` (no post type creation, no hooks referencing them). Building new import support for these extensions is separate feature work, not a compatibility fix, and is not covered by this spec. This narrows the Linear issue's stated "affected extensions" list to what the importer actually integrates with today.
- Multilingual linking will use WPML's officially documented, supported public compatibility APIs (its language/translation-group registration mechanism and translated-ID lookup), not direct database manipulation of WPML's tables.
- Taxonomy and custom-field translatability will be declared using WPML's standard plugin-compatibility declaration mechanism, so sites don't need extra manual admin configuration beyond installing/activating WPML.
- The WPML add-on that provides free-text "String Translation" is not assumed to be installed; any custom field values that need translation will rely on WPML's per-post custom-field translation configuration rather than depending on that separate add-on.
- Sites adopting this feature will have WPML configured (languages, translatable post types/taxonomies) before running multilingual imports; configuring WPML itself is out of scope, only ensuring the importer behaves correctly given a valid WPML setup.
- "No adverse impact on other platforms" (per the Linear acceptance criteria) is interpreted as: no regression in WETU Importer behavior on sites without WPML, and no regression in other Tour Operator Suite functionality unrelated to import.
- Compatibility work targets the current stable release lines of the WETU Importer, Tour Operator, and Tour Operator Team; supporting deprecated/unsupported versions is out of scope.
