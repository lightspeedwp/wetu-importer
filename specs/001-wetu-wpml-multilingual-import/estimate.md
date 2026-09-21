# Estimate: LS-4209 — WETU Importer WPML Multilingual Import Support

**Created**: 2026-09-21

**Based on**: [spec.md](./spec.md), [plan.md](./plan.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/wpml-integration-contract.md](./contracts/wpml-integration-contract.md), [quickstart.md](./quickstart.md)

**Produced by**: `lsx-agents:website-scope-estimator` agent, using the estimation framework/methodology in `agents/estimator-agent/AGENT.md` + `agents/estimator-agent/shared/core-prompt.md` (the paths originally pointed to, `agents/website-scope-estimator-agent/AGENT.md` and a Claude-specific `claude/agent.md`, do not exist in this repo — worth checking if a different file layout was intended there).

This is a backend compatibility fix to an existing plugin (v1.5.2) — no new project setup, no UI/design work, one net-new doc page only. Scope is already tightly bounded by the spec (TO Reviews/Specials explicitly excluded). Estimate assumes a single mid/senior WordPress PHP developer, already oriented on the plugin's codebase.

## Feature Breakdown (hours)

| # | Component | Hours | Notes |
|---|---|---|---|
| 1 | `class-wpml-compat.php` helper (detection, `wpml_loaded` bootstrap, `is_wpml_active()`, current/default/active-language resolution, `wpml_set_element_language_details` wrapper, `wpml_object_id`/`icl_object_id` wrapper, optional `icl_register_string` best-effort call) | 7–9 | Medium complexity; API surface is small and well-documented per research.md, but needs careful null/guard handling so it's a true no-op when WPML absent (FR-001/FR-011). |
| 2 | `wpml-config.xml` (custom-types, taxonomies, custom-fields declarations) | 2–3 | Straightforward once actual taxonomy list from Tour Operator/Team is enumerated — plan/research left the taxonomies list as a placeholder, so this carries a small residual research task into build. |
| 3 | **Language-aware existing-post matching** — `find_current_tours()`, `find_current_accommodation($post_type)`, `get_post_id_by_key_value()` (FR-005) | 8–10 | **Highest technical risk item.** Must preserve the existing `[wetu_id => post_id]` map contract consumed by the admin JS search-results table while adding a language filter/cross-check via `wpml_object_id`. Getting this wrong risks silently matching/updating the wrong language's post. |
| 4 | `import_row()` integration across `tours`/`accommodation`/`destination` classes + `team` creation path in base class, including cascade-created destination/accommodation trid linking during a tour import (Acceptance Scenario 1.4) | 13–16 | Four call sites, near-duplicated (no shared abstract base to hook into per research.md §3), so integration is repeated per class rather than written once. Cascade-linking order-of-operations (resolving `trid` before vs. after the cascade post exists) is the fiddly part here. |
| 5 | Fallback-to-default-language handling + user-facing message (FR-009) | 2–3 | Small, isolated logic + admin notice string. |
| 6 | **Automated tests** — `tests/test-wpml-compat.php` with stubbed WPML constants/hooks, plus explicit WPML-absent test path, per quickstart's "Automated coverage" list | 9–12 | **Second highest-risk item.** Stub harness must faithfully mimic `wpml_set_element_language_details`/`wpml_object_id`/`wpml_active_languages`/`wpml_current_language` contracts. Risk of false-confidence tests that pass against stubs but wouldn't hold against real WPML behavior. |
| 7 | Manual QA — quickstart Scenarios A–G against the real installed WPML v5.0.1 (multi-language site, cross-language matching, team extension, no-WPML regression, unconfigured-language fallback) | 6–8 | Necessary complement to #6 precisely because the stubs in #6 can't fully validate real WPML behavior. |
| 8 | Documentation — `docs/wpml-compatibility.md` + `changelog.md` compat entry (scope, exclusions, troubleshooting) | 3–4 | FR-010; needs to explicitly state Reviews/Specials exclusion per SC-005. |
| 9 | PR prep / code review response / branch, labels, changelog admin (per repo's `compat/` branch + Definition of Done conventions) | 3–4 | Process overhead, not pure dev time. |

**Core total: 53–69 hours** (midpoint ≈ 61h)

**+ contingency**: Given two explicitly flagged risk areas (items 3 and 6) plus the inherent "first time integrating this API in this codebase" unknowns, apply a 15–20% contingency on top rather than just widening ranges further.

## Overall Estimate

**≈ 65–80 hours** (≈ 8–10 developer-days), midpoint **~72 hours**.

This roughly maps to the standard effort-distribution model: Analysis/Design ~10% (mostly already spent producing these spec artefacts, so treat as sunk), Development ~55–60% (items 1–5), Testing/QA ~20–25% (items 6–7 — appropriately weighted high here given the risk profile), Review/Docs ~10–15% (items 8–9).

## Timeline

Solo developer, not full-time-dedicated (realistic for a compat fix alongside other work):

- **Week 1**: Items 1–3 (helper class, config XML, language-aware matching) — the foundation and highest-risk piece done first so problems surface early.
- **Week 2**: Item 4 (import_row integration across all 4 post types + cascade linking) + item 5 (fallback handling).
- **Week 2–3**: Item 6 (automated tests) run in parallel with/immediately after each integration point as it lands, not batched at the end.
- **Week 3**: Item 7 (manual QA against real WPML), item 8 (docs/changelog), item 9 (PR, review cycles).

**Realistic elapsed calendar time: 3 weeks**, assuming no major surprises in the two risk areas below and normal review-turnaround latency (not full-time headcount-hours — actual effort is ~65–80h against that 3-week window).

## Risks

1. **FR-005 language-aware matching (highest risk)**: Refactoring `find_current_tours()` / `find_current_accommodation()` / `get_post_id_by_key_value()` without breaking the existing `[wetu_id => post_id]` map contract the admin JS depends on. If the language filter is bolted on incorrectly, the importer could silently update the wrong language's post (data-model.md's core invariant). *Mitigation*: treat Quickstart Scenario D (cross-language matching correctness) as a hard gate before calling this feature done — both as an automated test and a manual re-run — not just a nice-to-have check.

2. **Testing WPML without it as a composer dependency (second highest risk)**: The PHPUnit stub layer (constants + filter/action stubs) can only be as good as the fidelity of the stubs against real WPML v5.0.1 behavior. Green tests against stubs don't guarantee correctness against the real plugin. *Mitigation*: explicitly pair every automated stub-based test with the corresponding manual quickstart scenario against the real installed WPML before sign-off — automated tests catch regressions on every future change, manual QA catches stub/reality drift on this pass.

3. **Cascade-created post linking (Acceptance Scenario 1.4)**: destination/accommodation posts created inside a tour import need consistent `trid` linking with the parent tour. Easy to get the order of operations wrong (resolving `trid` before the cascade post exists vs. after). Medium risk, contained within item 4's estimate but worth calling out as its own review-focus area.

4. **wpml-config.xml taxonomy list is still a placeholder** in the plan/contract artefacts — actual Tour Operator/Team taxonomies need enumerating during implementation. Low risk, small effort, but currently unresolved and could reveal an edge case (e.g. a taxonomy shared with a non-importer-created post type) not anticipated in the spec.

5. **Scope-creep pressure**: TO Reviews and TO Specials were deliberately excluded because they have no existing import integration. If a stakeholder pushes to include them mid-implementation, that is materially new feature work (not a compat fix) and would need to be re-scoped/re-estimated separately rather than absorbed into this timeline.
