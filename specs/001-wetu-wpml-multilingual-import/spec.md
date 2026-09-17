# Feature Specification: WETU Importer WPML Multilingual Import Support

**Feature Branch**: `feature/ls-4209-compat-wetu-importer-support-wpml-multilingual-imports`

**Created**: 2026-09-17

**Status**: Draft

**Input**: User description: "compat: WETU Importer - support WPML multilingual imports (LS-4209). Integrate WPML with the WETU Importer (https://github.com/lightspeedwp/wetu-importer) so Tour Operator Suite can import content in multiple languages. Affected extensions: Tour Operator, TO Reviews, TO Specials, TO Team. Expected behavior: The WETU Importer supports importing content in multiple languages through WPML. Acceptance criteria from Linear issue LS-4209: issue reproducible and documented, compatible behavior confirmed on affected platforms, no adverse impact on other platforms, documentation/changelog updated if needed, PR uses correct branch prefix (compat/), PR description updated with relevant details, changelog entry prepared, labels/types match org standards."

**Linear Issue**: [LS-4209](https://linear.app/lightspeedwp/issue/LS-4209/compat-wetu-importer-support-wpml-multilingual-imports)

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Import WETU content into a specific site language (Priority: P1)

A tour operator content editor running WPML runs the WETU Importer while the site's current admin language is set to a secondary language (e.g. German, on a site whose default language is English). The imported tour, destination, and related content is created as a translation correctly linked to the existing default-language content, rather than as a duplicate, untranslated, or orphaned post.

**Why this priority**: This is the core compatibility gap described in the issue — without it, WPML sites cannot use the importer at all without creating duplicate or disconnected content, which is the primary blocker for Tour Operator Suite customers who run multilingual sites.

**Independent Test**: Can be fully tested by configuring a WPML multilingual site with two languages, running the WETU Importer once per language for the same WETU item, and confirming both results appear as linked translations of one WPML translation group in the WordPress admin, with no duplicate default-language post created on the second run.

**Acceptance Scenarios**:

1. **Given** a WPML site with English as the default language and German as an active secondary language, **When** an editor imports a WETU tour while in the English admin language, **Then** the tour is created as the original ("source") post and registered with WPML in the site's default language.
2. **Given** the same WETU tour has already been imported in English, **When** an editor switches the admin language filter to German and re-runs the import for that same WETU item, **Then** the importer creates a German translation linked to the existing English post via WPML's translation relationships, instead of creating a second, unlinked English post.
3. **Given** a WETU import that includes taxonomy terms (e.g. destinations, activity types) and custom fields, **When** the import runs in a secondary language, **Then** taxonomy terms and translatable custom field values resolve to their existing translated equivalents where available, and register as translatable strings when no translation exists yet.

---

### User Story 2 - Import content for extension post types (Tour Operator, TO Reviews, TO Specials, TO Team) (Priority: P2)

A site running one or more of the affected Tour Operator Suite extensions (Tour Operator, TO Reviews, TO Specials, TO Team) imports content via the WETU Importer, and the resulting posts for each extension's post type are correctly registered with WPML the same way core Tour Operator content is.

**Why this priority**: The issue explicitly calls out these four extensions as affected; without per-extension compatibility, WPML support would be incomplete even if the core Tour Operator import path works.

**Independent Test**: Can be tested independently per extension by activating just that extension alongside WPML and the WETU Importer, importing relevant WETU content, and confirming the resulting post type is correctly linked into WPML's translation system.

**Acceptance Scenarios**:

1. **Given** the TO Reviews extension is active alongside WPML, **When** WETU review content is imported in a secondary language, **Then** the review post is linked as a WPML translation of the corresponding source-language review (or created as a new source post if none exists).
2. **Given** the TO Specials or TO Team extension is active alongside WPML, **When** content relevant to that extension is imported, **Then** the same translation-linking behavior applies consistently with Tour Operator core content.

---

### User Story 3 - Re-run imports without breaking existing translations (Priority: P3)

An editor re-runs the WETU Importer (e.g. a scheduled sync, or a manual refresh to pick up updated WETU data) on content that has already been imported and translated. Existing WPML translation links and any manually edited translated content are preserved rather than being overwritten or disconnected.

**Why this priority**: Import is often a recurring or repeatable operation. If re-imports break translation links or clobber human-edited translations, the feature would be unsafe to use in ongoing operation even if the first import works.

**Independent Test**: Can be tested by importing an item, manually editing the translated post, re-running the import for the same WETU item and language, and confirming the translation link remains intact and editor-made changes are handled per the defined update behavior (documented, not silently discarded without warning).

**Acceptance Scenarios**:

1. **Given** a WETU item has already been imported and translated, **When** the same item is re-imported in the same language, **Then** the existing translated post is updated in place and remains linked to the same WPML translation group (no duplicate post is created).
2. **Given** an editor has manually modified a translated post, **When** a re-import updates that same post from WETU source data, **Then** the behavior (update vs. skip vs. flag for review) is consistent and documented so editors know what to expect.

### Edge Cases

- What happens when WPML is installed but not active, or is active without any secondary languages configured? Import should behave as it does today (single-language), with no errors.
- What happens when the importer runs in a language that WPML is not configured to support? The importer should fall back to the site default language and surface a clear message rather than failing silently or creating misconfigured content.
- How does the system handle a WETU item that maps to content across multiple affected extensions simultaneously (e.g. a tour with linked reviews) during a multilingual import — are translation relationships kept consistent across the related posts?
- What happens when a taxonomy term or custom field required for translation does not yet have a WPML translation available — is the original-language value used as a fallback, and is that fallback clearly identifiable to editors?
- What happens if WPML's translation tables are in an inconsistent state (e.g. orphaned translation group) at the time of import — does the importer error out clearly instead of compounding the inconsistency?

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The WETU Importer MUST detect whether WPML is active and adapt its import behavior accordingly, with no change to current single-language behavior when WPML is inactive.
- **FR-002**: When WPML is active, the importer MUST register each newly imported post with WPML in the language that is active in the admin UI at the time of import.
- **FR-003**: When a WETU item is imported in a language other than its original import language, the importer MUST link the new post as a WPML translation of the existing source-language post rather than creating an unlinked duplicate.
- **FR-004**: The importer MUST apply the same WPML translation-linking behavior described in FR-002 and FR-003 to all affected post types: Tour Operator core content, TO Reviews, TO Specials, and TO Team.
- **FR-005**: The importer MUST resolve translatable taxonomy terms and custom fields to their existing translated equivalents when available, and MUST register untranslated taxonomy terms/strings so they appear in WPML's translation queue rather than being silently duplicated per language.
- **FR-006**: Re-running an import for a WETU item/language pair that has already been imported MUST update the existing linked post rather than creating a new, disconnected post.
- **FR-007**: The importer MUST NOT alter or break existing WPML translation relationships for content that was previously imported or manually translated.
- **FR-008**: When WPML is active but the current admin/import language is not one of the site's configured WPML languages, the importer MUST fall back to the site default language and present a clear message to the user explaining the fallback.
- **FR-009**: Compatibility behavior MUST be documented (in the WETU Importer repository's changelog and/or relevant docs) so support staff and integrators understand how multilingual import works.
- **FR-010**: The feature MUST NOT introduce regressions to WETU import behavior on sites that do not run WPML (verified via acceptance criteria "no adverse impact on other platforms").

### Key Entities

- **WETU Item**: External source content (tour, destination, review, special, team member) identified by a stable WETU ID, imported into WordPress as one or more posts.
- **WPML Translation Group**: The set of posts across languages that WPML considers to be translations of one another, linked via a shared translation group identifier.
- **Affected Post Type**: A WordPress post type registered by one of the four affected plugins (Tour Operator, TO Reviews, TO Specials, TO Team) that the importer can create or update.
- **Translatable Term/Field**: A taxonomy term or custom field value on an affected post type that WPML is configured to make translatable (e.g. via the WPML String Translation or Taxonomy Translation mechanisms).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: On a WPML-enabled site, importing the same WETU item once per configured language results in exactly one post per language, all correctly linked as translations of one another, with zero unlinked duplicate posts.
- **SC-002**: 100% of the four affected extensions (Tour Operator, TO Reviews, TO Specials, TO Team) exhibit the same correct translation-linking behavior when tested individually.
- **SC-003**: Re-running an import on previously imported and translated content preserves 100% of existing WPML translation links across at least 3 consecutive re-import cycles in testing.
- **SC-004**: Sites without WPML installed or active show no behavioral difference in import results before and after this change (zero regressions in non-WPML acceptance testing).
- **SC-005**: Support/documentation updates are published such that a new integrator can configure a multilingual WETU import without needing to contact engineering for clarification.

## Assumptions

- WPML (WordPress Multilingual Plugin), including its core translation-management APIs, is the only multilingual plugin in scope; other multilingual solutions (e.g. Polylang) are out of scope for this feature.
- The affected extensions (Tour Operator, TO Reviews, TO Specials, TO Team) each register standard WordPress post types and taxonomies that WPML can be configured to make translatable through its normal admin settings; no additional custom translation storage is assumed.
- Sites adopting this feature will have WPML configured (languages, translatable post types/taxonomies) before running multilingual imports; configuring WPML itself is out of scope, only ensuring the importer behaves correctly given a valid WPML setup.
- "No adverse impact on other platforms" (per the Linear acceptance criteria) is interpreted as: no regression in WETU Importer behavior on sites without WPML, and no regression in other Tour Operator Suite functionality unrelated to import.
- Compatibility work targets the current stable release lines of the WETU Importer and the four affected extensions; supporting deprecated/unsupported versions is out of scope.
