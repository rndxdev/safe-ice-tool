# Codebase Health Audit — Safe Ice Tool

A prioritized list of shortcomings found across backend correctness, security/authorization, data-model
integrity, testing/reliability, and frontend quality. Each item has a file reference, severity, the
problem, and a suggested fix so it can be worked through incrementally.

Audited 2026-06-15. Findings verified against the actual code, not speculation.

**Convergence note:** the ice-report voting flaw (C1) was independently flagged by three separate
analyses (backend, security, data-model). The "no tests / no factories" gap (H3) was flagged by both
testing and backend reviews. These are the highest-confidence issues.

---

## 🔴 Critical — fix before building new features

### C1 — Ice-report voting can be gamed to hide safety-critical reports
- **Where:** `app/Http/Controllers/IceReportController.php:72-102`; routes `routes/web.php:126-127`;
  no `ice_report_votes` table in `database/migrations/`.
- **Problem:** `upvote()`/`downvote()` are blind `$report->increment(...)` calls with **no per-user
  vote record and no unique constraint** (unlike `feed_reactions`/`comment_likes`/`lake_verifications`
  which all have `unique(user_id, …)`). One authenticated user can POST `/reports/{id}/downvote` 5×
  in a loop to hit the −5 `maybeModerate()` auto-hide threshold and suppress a "thin ice / open water"
  warning — or inflate upvotes to fake consensus. Safety-critical in an ice-safety app.
- **Also:** the increment → `refresh()` → conditional `update()` in `maybeModerate` is **not atomic**
  (race condition under concurrent votes).
- **Fix:** add an `ice_report_votes` table with `unique(ice_report_id, user_id)`; toggle/switch one
  vote per user and recompute `upvotes`/`downvotes` from it. Wrap the moderation read-decide-write in
  a transaction with `lockForUpdate()`.

### C2 — Polymorphic feed leaks data across deleted entities (ID reuse)
- **Where:** `feed_reactions`, `feed_comments`, `feed_acknowledgements`, `comment_likes`
  (migrations `2026_01_27_000001`–`000004`). No observers / `deleting` hooks exist (grep-confirmed).
- **Problem:** these reference parents only by string `(item_type, item_id)` / `(comment_type,
  comment_id)` — no FK, no cleanup. When an IceReport/TripPost/Trip/Lake is deleted, its interaction
  rows become permanent orphans. Because IDs are reused per table, a future `TripPost #5` inherits the
  likes/comments of a deleted `post #5` — **cross-entity data bleed**.
- **Roadmap impact:** catch reports and `condition_reports` will plug into this same feed, so the bug
  compounds as content types grow. Fix this before extending the feed.
- **Fix:** add model `deleting` hooks / Observers on IceReport, TripPost, Trip, Lake, FeedComment that
  delete matching `feed_*` / `comment_likes` rows by key. (Longer term: a real `morphs()` relation.)

---

## 🟠 High — systemic gaps

### H1 — No rate limiting (register / vote / comment / report)
- **Where:** `routes/web.php:75-155` (whole `auth,verified` group has no `throttle`);
  `routes/auth.php:17` (register). Only login + email-verify are throttled.
- **Problem:** mass account creation, vote amplification (compounds C1), comment + @mention spam,
  fake-report flooding to skew `LakeSafetyService`.
- **Fix:** `throttle:` middleware — e.g. `throttle:10,1` on votes/comments/reports, `throttle:5,1`
  on register.

### H2 — Mass-assignment footguns on moderation/share fields
- **Where:** `app/Models/IceReport.php:13-26` (`is_flagged`, `is_hidden` fillable);
  `app/Models/TripPost.php:11-20` (`share_token` fillable).
- **Problem:** not exploitable today (controllers don't pass them), but one future
  `create($request->...)`/`update($validated)` away from letting a user un-hide their downvoted report
  or set a guessable share token.
- **Fix:** remove `is_flagged`/`is_hidden` from IceReport fillable and `share_token` from TripPost
  fillable; set them only via explicit server-side assignment.

### H3 — App is effectively untested; missing factories break at runtime
- **Where:** `tests/` (100% Breeze auth scaffolding); `database/factories/` (only `UserFactory`).
- **Problem:** zero coverage for lakes, ice reports, trips, posts, comments, community feed, feed
  interactions, lake verification, notifications, @mentions, public share links, and **`LakeSafetyService`**
  (the core scoring algorithm). `Lake`/`IceReport`/`Trip` declare `use HasFactory` but have **no
  factory class** → `Lake::factory()` throws at runtime. No CI, no static analysis, no frontend tests.
- **Fix (priority order):**
  1. Factories for Lake, IceReport, Trip, TripPost, TripPostComment + feed models (with states like
     `IceReportFactory::thinIce()`/`hidden()`).
  2. Unit-test `LakeSafetyService` — every threshold band, the 0–100 clamp, empty path, 3 labels.
  3. GitHub Actions CI running `composer test` + `pint --test`.
  4. Feature-test voting auto-hide, dashboard feed enrichment, public share links.
  5. Add Larastan + `pint.json`; Vitest for the Vue layer.
  6. A demo seeder (users + reports across bands + trips + posts + feed activity).

### H4 — `/p/{token}` public post share leaks the full model
- **Where:** `app/Http/Controllers/TripPostShareController.php:28-34` (passes whole `$post` to Inertia).
- **Problem:** serializes every column (`user_id`, `trip_id`, `lake_id`, `share_token`, tags) to
  unauthenticated visitors. The trip share route (`web.php:47-67`) correctly whitelists; this doesn't.
- **Fix:** explicitly whitelist returned fields; never echo `share_token` back.

### H5 — Missing indexes on hot query paths
- **Where:** migrations for `ice_reports`, `lakes`, and the feed tables.
- **Problem:**
  - `ice_reports` queried by `lake_id` + `is_hidden` + `latest()` — only `lake_id` indexed.
  - `lakes` listing filters `is_active` + `status` — `is_active` unindexed, no composite.
  - feed tables queried by `(item_type, item_id)` and `user_id` — verify/add composite indexes.
- **Fix:** `ice_reports` → index `(lake_id, is_hidden, created_at)`; `lakes` → index
  `(is_active, status)`; feed tables → composite `(item_type, item_id)`.

### H6 — `lakes.created_by_user_id` has no foreign key
- **Where:** `2025_12_13_231727_add_status_and_creator_to_lakes_table.php` (plain `unsignedBigInteger`,
  index only).
- **Fix:** add `->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete()`.

---

## 🟡 Medium — performance, correctness, quality

### M1 — N+1 query in lake-index safety computation
- **Where:** `app/Http/Controllers/LakeController.php:96-101` + `app/Services/LakeSafetyService.php:12-14`.
- **Problem:** `computeForLake` runs one `iceReports()` query per lake across the full approved list.
- **Fix:** eager-load reports windowed to 10 days; have the service accept an in-memory collection.

### M2 — Counts loaded by hydrating full rows
- **Where:** `DashboardController.php:264-277, 292-298`; `LakeController.php:228-247`;
  `LakeVerificationController.php:42`.
- **Problem:** like/comment counts fetch every matching row into a Collection just to `count()` in PHP;
  `buildFeedCommentsByItem` loads all comments ever (no recency bound) then takes 5.
- **Fix:** DB aggregation (`selectRaw('item_type, item_id, count(*)')->groupBy(...)`); bound the
  comment query.

### M3 — Unguarded external HTTP call can 500 a request
- **Where:** `app/Services/ReverseGeoCodeService.php:19-25`, called from `LakeController::store:45`
  and `show → backfillLakeRegion:168`.
- **Problem:** `Http::timeout(8)->get(...)` to the US Census API has no try/catch; a
  `ConnectionException` (DNS/refused/timeout) is unhandled → lake create/view 500s when Census is down.
- **Fix:** wrap in try/catch, log, return `null` (callers already handle null).

### M4 — Fat controller; feed logic belongs in a service
- **Where:** `DashboardController.php` (347 lines). CLAUDE.md says business logic → `app/Services/`.
- **Fix:** extract a `CommunityFeedService` (also dedupes M2 fixes). Ties into the roadmap.

### M5 — Massive duplication in the 5 `buildXxxFeedItems` methods
- **Where:** `DashboardController.php:84-239` — near-identical lake/user shaping + fallbacks (~150 lines).
- **Fix:** a shared `makeFeedItem(...)` helper + small per-type closures (~40 lines).

### M6 — No enum/lookup constraint on categorical fields
- **Where:** `ice_reports.ice_type` / `traffic_type` (IceReportController:20-21), `trips.time_of_day`
  (TripController:48). Freeform strings with no whitelist in DB *or* code.
- **Problem:** data drift ("Clear" vs "clear"), breaks UI grouping; pollutes data ahead of the
  season-aware reporting work.
- **Fix:** `Rule::in([...])` validation + PHP enums/casts; consider DB CHECK constraints.

### M7 — Inline validation duplicated instead of Form Requests
- **Where:** IceReportController, LakeController, TripController (rules copy-pasted in `store` +
  `validateTripPayload`), FeedInteractionController (the `ALLOWED_TYPES` block repeated 3×). Only
  `LoginRequest` + `ProfileUpdateRequest` exist.
- **Fix:** extract `StoreIceReportRequest`, `StoreTripRequest`, `FeedInteractionRequest`, etc.

### M8 — Lake self-approval via sockpuppets
- **Where:** `LakeVerificationController.php:18-66`. Creator correctly blocked from self-verifying and
  `unique(lake_id, user_id)` enforces one vote — but approval needs only 2 approvals + 1 report.
- **Problem:** with no registration throttle (H1), a creator registers 2 puppets + 1 report to approve
  their own (possibly fake) lake into the public list; same to reject a legit lake.
- **Fix:** raise thresholds, weight by account age/reputation; at minimum gate registration (H1).

### M9 — `parent_id` self-reference: no FK, no depth/cycle guard
- **Where:** `2026_01_27_000005_add_parent_id_to_comments_tables.php` (`feed_comments`,
  `trip_post_comments`).
- **Problem:** plain nullable `unsignedBigInteger`, no self-FK; nothing prevents replying to a reply →
  arbitrary-depth chains, while the frontend assumes flat-ish nesting.
- **Fix:** self-referential FK with `nullOnDelete`; enforce single-level replies in code if flat
  threading is intended.

### M10 — Dashboard.vue monolith + hardcoded data + no composables
- **Where:** `resources/js/Pages/Dashboard.vue` (1,134 lines); hardcoded 18-state DNR map (41-150);
  date-format logic duplicated across 9 files; no `resources/js/Composables/` dir.
- **Problem:** unmaintainable/untestable; DNR URL fixes require a frontend redeploy; the 18-state map
  silently drops unmatched regions.
- **Fix:** covered in detail by `DASHBOARD-PAGE-REFACTOR.md` — decompose into components, extract
  composables (`useDateFormat`, `useToast`, `useShare`, `useGeolocation`), move state data to backend.

---

## 🟢 Low — hardening & polish

- **L1** — IceReport model has no `$casts`; booleans serialize to the frontend as `0/1`, decimals as
  strings (`app/Models/IceReport.php`). Add a `$casts` block.
- **L2** — `Trip::$fillable` missing `is_public`/`share_token` (set via direct assignment today, but a
  latent trap) (`app/Models/Trip.php:13-23`).
- **L3** — `@mention` regex `/@([a-zA-Z0-9_-]+)/` over-matches email-like text →spurious mentions
  (`MentionNotificationService.php:97`). Add a leading-boundary check.
- **L4** — File-upload validation uses extension-based `mimes:` rather than content-based `mimetypes:`
  (`TripPostController.php:178-179`). Low-risk hardening.
- **L5** — No frontend lint/format tooling; `.editorconfig` says 4-space indent but Dashboard.vue uses
  2-space. Add ESLint + Prettier + `lint`/`format` scripts.
- **L6** — `alert()`/`prompt()` (8×) and a raw `<a href>` internal link in `NotificationCenter.vue:236`
  break SPA UX. Route through toast + Inertia `<Link>`.
- **L7** — Accessibility: color-only ice-thickness/safety badges, icon-only share buttons with `title`
  but no `aria-label`, clickable `<li>` rows with no keyboard handler, empty `alt` on gallery media.
- **L8** — `UserProfileController::show` 404-vs-200 is a username-enumeration oracle; `reports_count`/
  `lakes_count` ignore `profile_visibility`. Minor.
- **L9** — Slug generation race in `LakeController::uniqueSlug` (no lock) can collide on the unique
  index → unhandled `QueryException`. Rare.
- **L10** — Remove stub `ExampleTest` files; gitignore `.phpunit.result.cache`.

---

## Confirmed *fine* (checked, no action needed)
- `LakeSafetyService` 10-day window + scoring math: internally consistent, division-by-zero guarded,
  `max(0, min(100, …))` clamps correctly. Risk is performance (M1), not correctness — but it has **no
  tests** (H3), so pin the behavior before refactoring.
- Share tokens: `Str::random(32)` (~190 bits) in `varchar(64) unique` — not brute-forceable.
- `/t/{token}` trip share: correctly whitelists fields, no PII leak (contrast H4).
- File uploads: random hashed filenames, size/count limits, `public` disk — no path traversal.
- Ownership checks on trip/post/notification/profile update+share endpoints: present, no IDOR.
  (Note: there are **no delete endpoints** for trips/posts/reports/comments — a feature gap, not a vuln.)
- `notifications` indexes (`user_id, read_at` and `user_id, created_at`): adequate.
- `decimal` precision on thickness (4,1) and lat/lng (10,7): adequate.

---

## Suggested order of attack
1. **C1, C2** — the safety-critical prerequisites; both block the year-round feed expansion.
2. **H3** — factories + `LakeSafetyService` tests + CI: the safety net that makes every later fix safe
   (this is roadmap **Phase 0**).
3. **H1, H2, H4** — security hardening (rate limits, fillable, share payload).
4. **H5, H6, M1–M3** — performance + integrity (indexes, FK, N+1, external-call guard).
5. **M4–M10** — refactors (feed service, Form Requests, enums, dashboard) — overlap with the
   dashboard refactor and roadmap.
6. **Low** — polish as you touch the surrounding code.
