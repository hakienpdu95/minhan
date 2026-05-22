# Survey Module Audit Report

**Date:** 2026-05-22  
**Auditor:** Claude Code  
**Scope:** `Modules/Survey/` only — no schema/migration changes

---

## Group 1 — Validate Submit

| # | Task | Status | Notes |
|---|------|--------|-------|
| 1.1 | Status check before submit | ✅ | `SubmitSurveyAction::handle()` throws `SurveyNotActiveException` (403) if not Active |
| 1.2 | `SurveyNotActiveException` | ✅ | `Modules/Survey/app/Exceptions/SurveyNotActiveException.php` — renders JSON 403 |
| 1.3 | Controller loads survey without active filter | ✅ | `SurveyController::submit()` uses `Survey::bySlug($slug)->firstOrFail()` |
| 1.4 | Duplicate submit warning | ✅ | `SubmitSurveyAction` logs `Log::warning('survey.duplicate_submit')` if same `respondent_ref` within 5 min |
| 1.5 | is_other + is_required validation | ✅ | `layer3()` in `SubmitSurveyAction` validates other-text on required other-option fields |

---

## Group 2 — Data Integrity

| # | Task | Status | Notes |
|---|------|--------|-------|
| 2.1 | Slug: UUID hash suffix | ✅ | `CreateSurveyAction::uniqueSlug()` generates `{title-slug}-{8hexchars}` |
| 2.2 | Slug: no manual input | ✅ | `SurveyFormData` has no slug field; create/edit forms display slug read-only |
| 2.3 | `FieldImmutableException` | ✅ | `Modules/Survey/app/Exceptions/FieldImmutableException.php` — renders JSON 422 |
| 2.4 | Granular field lock on active survey | ✅ | `UpdateFieldAction`: allows label/placeholder changes; blocks structural changes (is_required, rules) |
| 2.5 | `SurveyObserver` — deactivate tokens on delete | ✅ | `SurveyObserver::deleting()` calls `$survey->tokens()->update(['is_active' => false])` |
| 2.6 | Mass assignment protection | ✅ | Removed `is_active` from `SurveyField`, `status` from `SurveyResponse`, `token` from `SurveyToken` |

---

## Group 3 — Security

| # | Task | Status | Notes |
|---|------|--------|-------|
| 3.1 | Cloudflare Turnstile middleware | ✅ | `ValidateSurveyTurnstile` — skips in local/testing/disabled; applied to submit route |
| 3.2 | Token generation: unique, retried | ✅ | `GenerateSurveyTokenAction::generateUniqueToken()` — max 3 retries, throws `RuntimeException` on failure |
| 3.3 | `ActivateSurveyAction` guards | ✅ | Checks: ≥1 section, every section has ≥1 active field, required choice fields have options |

---

## Group 4 — Performance

| # | Task | Status | Notes |
|---|------|--------|-------|
| 4.1 | Redis schema cache | ✅ | `BuildSurveySchemaAction` — `Cache::store('redis')->remember(…, 3600, …)` |
| 4.2 | Cache purge on mutation | ✅ | `purgeCache()` called in `CreateOptionAction`, `UpdateOptionAction`, `UpdateFieldAction`, `DeactivateFieldAction` |
| 4.3 | Filter choice fields with no options | ✅ | `filterSchema()` removes empty-option choice fields, logs warning |
| 4.4 | Filter empty sections | ✅ | `filterSchema()` removes sections with no remaining active fields |
| 4.5 | `ResponseViewerService` N+1 fix | ✅ | Option map built from eager-loaded field options; answers resolved in-memory (4 queries total) |

---

## Group 5 — Edge Cases

| # | Task | Status | Notes |
|---|------|--------|-------|
| 5.1 | `DeactivateFieldAction` cache purge | ✅ | Looks up slug via `Survey::where('id', …)->value('slug')`, then purges |
| 5.2 | `UpdateSurveyAction` no slug field | ✅ | Only updates `title` and `version` |
| 5.3 | `GuardsSurveyIntegrity` uses `FieldImmutableException` | ✅ | Destroy actions throw 422 `FieldImmutableException` instead of `ValidationException` |

---

## Group 6 — Testing

| # | Task | Status | Notes |
|---|------|--------|-------|
| 6.1 | Happy path (201, answer count) | ✅ | `test_submit_returns_201_with_response_id_on_valid_payload` — asserts 5 answer rows |
| 6.2 | Reject unknown field_key (422) | ✅ | `test_submit_returns_422_for_unknown_field_key` + duplicate key test |
| 6.3 | Reject invalid option_value (422) | ✅ | Radio, checkbox, and array-to-radio tests |
| 6.4 | Missing required field (422) | ✅ | Missing field + empty string tests |
| 6.5 | Submit closed/draft survey (403) | ✅ | `test_submit_returns_403_for_draft_survey` + `test_submit_returns_403_for_closed_survey` |
| 6.6 | Token invalid (401) | ✅ | No token, invalid token, inactive token — all assert 401 |
| 6.7 | Unit `AnswerValueResolver` | ✅ | 16 unit tests — all pass (pure unit, no DB/framework deps) |

**Test results:** 32 / 32 Survey tests passing (16 Feature + 16 Unit)

---

## Files Changed

```
Modules/Survey/app/Actions/ActivateSurveyAction.php
Modules/Survey/app/Actions/BuildSurveySchemaAction.php
Modules/Survey/app/Actions/CreateFieldAction.php
Modules/Survey/app/Actions/CreateOptionAction.php
Modules/Survey/app/Actions/CreateSurveyAction.php
Modules/Survey/app/Actions/DeactivateFieldAction.php
Modules/Survey/app/Actions/GenerateSurveyTokenAction.php
Modules/Survey/app/Actions/SubmitSurveyAction.php
Modules/Survey/app/Actions/UpdateFieldAction.php
Modules/Survey/app/Actions/UpdateOptionAction.php
Modules/Survey/app/Actions/UpdateSurveyAction.php
Modules/Survey/app/Data/SurveyFormData.php
Modules/Survey/app/Exceptions/FieldImmutableException.php          [NEW]
Modules/Survey/app/Exceptions/SurveyNotActiveException.php         [NEW]
Modules/Survey/app/Http/Controllers/SurveyController.php
Modules/Survey/app/Http/Middleware/ValidateSurveyTurnstile.php      [NEW]
Modules/Survey/app/Http/Requests/Admin/SurveyRequest.php
Modules/Survey/app/Models/SurveyField.php
Modules/Survey/app/Models/SurveyResponse.php
Modules/Survey/app/Models/SurveyToken.php
Modules/Survey/app/Observers/SurveyObserver.php                     [NEW]
Modules/Survey/app/Providers/SurveyServiceProvider.php
Modules/Survey/app/Services/ResponseViewerService.php
Modules/Survey/app/Support/GuardsSurveyIntegrity.php
Modules/Survey/resources/views/surveys/create.blade.php
Modules/Survey/resources/views/surveys/edit.blade.php
Modules/Survey/routes/api.php
Modules/Survey/tests/Feature/SubmitSurveyTest.php
```

---

## No Schema Changes

All changes are code-only. No new migrations were created or required.
