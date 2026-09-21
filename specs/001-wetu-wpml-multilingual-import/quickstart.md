# Quickstart: Validating WETU Importer WPML Compatibility

Manual + automated validation scenarios proving the feature works end-to-end. Maps to the acceptance scenarios in [spec.md](./spec.md) and the contract in [contracts/wpml-integration-contract.md](./contracts/wpml-integration-contract.md).

## Prerequisites

- Local WordPress install with:
  - `tour-operator` plugin active (required by `wetu-importer`)
  - `wetu-importer` plugin active, on this feature branch
  - `sitepress-multilingual-cms` (WPML core) active and configured with at least two languages (e.g. English default, German secondary)
  - Optionally, Tour Operator Team extension active, to validate `team` post handling
- A test WETU account/API credentials with at least one tour whose itinerary references at least one destination/accommodation not yet imported (to validate cascade-created posts)

## Scenario A — First import in the default language (source post)

1. In wp-admin, switch the admin language switcher to the site default language (e.g. English).
2. Run the WETU Importer, search for a known tour, and import it.
3. **Expect**: a new `tour` post is created; in WPML's Translations column/screen, it appears as the source/original with no listed translations yet.
4. Repeat for any cascade-created `destination`/`accommodation` posts from the same import — each should also appear as a source post in English.

## Scenario B — Import the same item in a secondary language

1. Switch the admin language switcher to the secondary language (e.g. German).
2. Run the WETU Importer again for the **same** tour (same WETU ID).
3. **Expect**: a new `tour` post is created in German, shown in WPML as a translation of the English post from Scenario A (same translation group) — not a second, disconnected English post.
4. Confirm via WPML's admin translation screen that both posts share one translation group.

## Scenario C — Re-import without breaking existing translations

1. Manually edit the German post's title (simulate editor customization).
2. Re-run the import for the same WETU ID and language (German).
3. **Expect**: the same German post is updated (not duplicated); it remains linked to the same translation group. Document whatever the chosen update behavior is (full overwrite vs. preserving manual edits) so this step's actual expected title is unambiguous before running it.

## Scenario D — Cross-language matching correctness

1. With both the English and German posts from Scenarios A/B existing, re-import the same WETU ID once more while the admin language switcher is set to English.
2. **Expect**: the English post is updated — the German post is untouched, and no new post is created in either language.
3. Repeat with the switcher set to German, expecting the German post to be the one updated.

## Scenario E — Team extension (if installed)

1. Ensure the Tour Operator Team extension is active.
2. Import WETU content that includes team members while in English, then again while in German.
3. **Expect**: the same source/translation linking behavior as Scenario A/B applies to the resulting `team` posts.

## Scenario F — No WPML installed (regression check)

1. Deactivate WPML entirely.
2. Run the existing importer test suite / a manual import of a tour.
3. **Expect**: behavior identical to pre-feature baseline — no WPML-related errors, warnings, or notices; no attempt to call WPML hooks.

## Scenario G — WPML active, unconfigured language

1. With WPML active, set the admin language switcher (if possible) or simulate an import request for a language code not present in `wpml_active_languages`.
2. **Expect**: the importer falls back to the site default language and shows a clear message explaining the fallback, rather than silently importing under an invalid language code or failing.

## Automated coverage

Run `vendor/bin/phpunit` (per `phpunit.xml.dist`). New test file `tests/test-wpml-compat.php` should cover, using stubbed WPML constants/hooks (see [research.md](./research.md#4-testing-approach-given-wpml-is-not-a-composer-dependency)):

- Language-aware lookup returns the correct post per language, given two posts sharing one `lsx_wetu_id`.
- `wpml_set_element_language_details` is called with the expected arguments for a first (source) import vs. a subsequent (translation) import.
- No WPML-related function/hook is invoked at all when `ICL_SITEPRESS_VERSION` is undefined.
- Fallback-to-default-language path when the resolved language isn't in the active languages list.
