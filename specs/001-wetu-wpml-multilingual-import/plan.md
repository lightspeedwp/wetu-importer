# Implementation Plan: WETU Importer WPML Multilingual Import Support

**Branch**: `feature/ls-4209-compat-wetu-importer-support-wpml-multilingual-imports` | **Date**: 2026-09-17 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `specs/001-wetu-wpml-multilingual-import/spec.md`

## Summary

Make the WETU Importer WPML-aware so that importing WETU content while a secondary WPML language is active creates a linked translation (same translation group) instead of an unlinked duplicate post. This applies to every post type the importer currently creates (`tour`, `destination`, `accommodation`, and `team` when Tour Operator Team is active), including posts cascade-created during a tour import. The existing WETU-ID-based existing-post lookup (`lsx_wetu_id` postmeta, via `find_current_tours()` / `find_current_accommodation()` / `get_post_id_by_key_value()`) must become language-aware so it never matches/updates a post belonging to a different language. Integration uses WPML's officially documented public compatibility API (`wpml_set_element_language_details` action, `wpml_object_id`/`icl_object_id`, `wpml_active_languages`, `wpml_current_language`, `wpml_loaded`) plus a `wpml-config.xml` declaring the plugin's custom post types/taxonomies as translatable, so no manual WPML admin configuration is required beyond installing/activating WPML. TO Reviews and TO Specials are out of scope (no existing import integration to extend).

## Technical Context

**Language/Version**: PHP 8.0+ (plugin header `Requires PHP: 8.0`; note `composer.json` dev requirement of `>=8.2` is a pre-existing inconsistency, not introduced by this feature — flagged for the team but not fixed here unless it blocks CI).

**Primary Dependencies**: WordPress 6.7+ (tested to 7.0), Tour Operator plugin (required, `Requires Plugins: tour-operator`), optionally Tour Operator Team (`LSX_TO_Team`, detected via `class_exists`/`post_type_exists('team')`), optionally WPML "WPML Multilingual CMS" core plugin (`sitepress-multilingual-cms`, developed/tested against v5.0.1, detected via `defined('ICL_SITEPRESS_VERSION')`).

**Storage**: WordPress core tables (`wp_posts`, `wp_postmeta` for `lsx_wetu_id`/`lsx_wetu_modified_date`) plus WPML's own translation tables (`icl_translations`, etc.), written to exclusively through WPML's public API — this feature does not read or write WPML's tables directly.

**Testing**: PHPUnit (`phpunit.xml.dist`, `tests/` dir, currently only `tests/test-itinerary-featured-image.php` exists). New tests will need a WPML stub/mock layer since WPML core is not a composer dependency and is not guaranteed present in the test environment; tests must also pass with WPML entirely absent (constants/functions undefined) to prove FR-001/FR-011 (no behavior change when WPML is inactive).

**Target Platform**: WordPress plugin (server-side PHP, wp-admin AJAX-driven importer UI plus Action Scheduler-based background sync in `class-wetu-automation.php`).

**Project Type**: WordPress plugin (single project, no frontend/backend split).

**Performance Goals**: No new hard performance target; must not measurably slow down existing import throughput (the importer already processes items in a queue via `queue_item()`/AJAX batches). WPML API calls (`do_action('wpml_set_element_language_details', ...)`) are lightweight synchronous calls and should add negligible per-item overhead.

**Constraints**: Must not change behavior on non-WPML sites (FR-011). Must not alter or break existing WPML translation relationships for previously imported/manually translated content (FR-008). Must work through the existing AJAX-driven import flow (`process_ajax_search()` → `process_ajax_import()` → `import_row()`) and the existing background sync path without duplicating logic in two places.

**Scale/Scope**: Four affected post types (`tour`, `destination`, `accommodation`, `team`), each with its own `import_row()`-equivalent in `class-lsx-wetu-importer-tours.php`, `-accommodation.php`, `-destination.php`, and the shared base class (`class-lsx-wetu-importer.php`) for `team` creation.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

`.specify/memory/constitution.md` in this project is still the unfilled template (no ratified project-specific principles). No project-specific gates apply beyond the org-wide conventions already in force for this repo (WPCS coding standards per `.phpcs.xml`, PHPUnit testing per `phpunit.xml.dist`, `compat/`-prefixed branch and changelog entry per the Linear issue's Definition of Done). This plan follows those existing conventions; no violations to justify.

## Project Structure

### Documentation (this feature)

```text
specs/001-wetu-wpml-multilingual-import/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
│   └── wpml-integration-contract.md
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

### Source Code (repository root)

```text
wetu-importer/
├── wpml-config.xml                              # NEW: declares tour/destination/accommodation/team + their
│                                                 #      taxonomies and custom fields as translatable
├── classes/
│   ├── class-lsx-wetu-importer.php              # Base class: shared WPML helpers added here
│   │                                             #   (is_wpml_active(), current language resolution,
│   │                                             #   language-aware get_post_id_by_key_value())
│   ├── class-lsx-wetu-importer-tours.php        # import_row(): register language + link translation
│   ├── class-lsx-wetu-importer-accommodation.php# import_row(): register language + link translation
│   ├── class-lsx-wetu-importer-destination.php  # import_row(): register language + link translation
│   ├── class-wetu-automation.php                # background sync: reuses same import_row() paths,
│   │                                             #   no separate WPML logic needed if base helpers are shared
│   └── class-wpml-compat.php                    # NEW: small dedicated helper class encapsulating all
│                                                  #   WPML API calls (detection, set language details,
│                                                  #   translated-ID lookup, language-aware existing-post
│                                                  #   lookup) so the importer classes stay WPML-agnostic
│                                                  #   apart from calling this helper
├── docs/
│   └── wpml-compatibility.md                    # NEW: documents scope (what's covered / TO Reviews &
│                                                  #   Specials excluded), behavior, and troubleshooting
├── changelog.md                                  # Updated with compat entry
└── tests/
    └── test-wpml-compat.php                      # NEW: PHPUnit coverage for the new helper class,
                                                    #   including a "WPML absent" no-op path
```

**Structure Decision**: Introduce one new, narrowly-scoped helper class (`class-wpml-compat.php`) rather than scattering `defined('ICL_SITEPRESS_VERSION')` checks and WPML API calls across each of the three existing importer classes and the base class. Each `import_row()` method gains a small, consistent call to this helper immediately after `wp_insert_post()`/`wp_update_post()`. The helper also replaces the language-blind parts of the existing lookup helpers (`find_current_tours()`, `find_current_accommodation()`, `get_post_id_by_key_value()`) with a language-aware variant, keeping the existing SQL-based approach but adding a language filter join. A `wpml-config.xml` file at the plugin root is the standard, zero-admin-config way to declare the plugin's custom post types/taxonomies/custom fields as translatable to WPML.

## Complexity Tracking

*No constitution violations — this section is not applicable.*
