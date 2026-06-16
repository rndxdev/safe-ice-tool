# Dashboard Route and Page Refactor

This document explains the current dashboard architecture, the problems with it, and how the
refactor must be implemented so we don't break Inertia + Vue navigation along the way.

It is a **plan**, not a record of completed work. Nothing here has been applied to the codebase yet.

---

## Goals

1. **Break up `Dashboard.vue`** — the page is ~1,134 lines and renders ~8 unrelated sections inline.
   Extract each section into a focused child component and move client-side logic into composables.
2. **Fix / preserve Inertia + Vue navigation** — all in-app navigation must stay SPA-style
   (`<Link>` / `router.visit`). No full-page reloads, no broken back-button behavior.
3. **Restructure the route / controller** — move the community-feed assembly out of
   `DashboardController` into a dedicated service so the controller just orchestrates.
4. **Move hardcoded data out of the template** — the 19-state DNR resource map and the static
   ice-safety guide content live inside `Dashboard.vue` and should move to a data module / config.

---

## Current Architecture (as-is)

### Route
`routes/web.php:77`
```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // feed interaction routes: feed.acknowledge, feed.like, feed.comment, comments.like ...
});
```
- `/dashboard` is the primary authenticated landing page. Every post-login / register / verify flow
  redirects here (`AuthenticatedSessionController:36`, `RegisteredUserController:51`,
  `VerifyEmailController:18,25`).
- `/` serves the `Welcome` page for guests; there is **no** auto-redirect from `/` to `/dashboard`.

### Controller
`app/Http/Controllers/DashboardController.php` (348 lines)

`index()` passes 5 props to `Inertia::render('Dashboard', ...)`:
| Prop | Source |
| --- | --- |
| `user` | `Auth::user()->only(['id','name','username','email'])` |
| `favoriteLakes` | `user->favoriteLakes()` (id, name, slug, region) |
| `upcomingTrips` | next 5 `Trip` by `trip_date` |
| `recentReports` | latest 5 `IceReport` on favorite lakes (`loadRecentReports`) |
| `communityFeed` | merged + enriched feed, 12 items |

The bulk of the file is feed assembly:
- `buildReportFeedItems()` / `buildPostFeedItems()` / `buildCommentFeedItems()` /
  `buildTripShareFeedItems()` / `buildLakeFeedItems()` — each queries one model and maps to a
  normalized `{type, id, created_at, title, message, lake, user, url}` shape.
- `enrichFeedWithInteractions()` — bulk-loads acknowledgements, likes, like counts, comment counts.
- `buildFeedCommentsByItem()` — bulk-loads feed comments + comment likes, groups by item.

This is well-factored *internally* but is too much responsibility for a controller.

### Vue Page
`resources/js/Pages/Dashboard.vue` — uses `AuthenticatedLayout`, props match the 5 above.

Inline sections (approx. line ranges in current file):
1. State selector + geolocation auto-detect (Nominatim reverse geocode, localStorage) — 513–534
2. Community feed (likes / comments / acknowledge, uses `CommentThread`) — 536–646
3. Quick actions bar — 648–673
4. Favorite lakes grid (+ social share) — 678–745
5. Upcoming trips grid — 747–811
6. Recent reports grid — 813–869
7. Safety & resources (hardcoded 19-state DNR map at lines 41–~250, safety guide) — 873–1053
8. Emergency safety banner — 1056–1128

Only `CommentThread` is currently extracted; everything else is inline markup + script logic.

---

## Target Architecture (to-be)

### Backend

Introduce `app/Services/CommunityFeedService.php` and move all feed-building private methods there:

```
DashboardController::index()
  ├─ $favoriteLakes / $upcomingTrips / $recentReports  (stay in controller — simple, dashboard-specific)
  └─ app(CommunityFeedService::class)->forUser($user, limit: 12)
        ├─ buildReportFeedItems()  ...  buildLakeFeedItems()
        ├─ enrichFeedWithInteractions()
        └─ buildFeedCommentsByItem()
```

- The service returns the same normalized array shape the Vue side already consumes — **no prop
  contract change**, so the frontend refactor and backend refactor are independent and can land
  separately.
- `LakeSafetyService` / `ReverseGeoCodeService` already establish the `app/Services` pattern; follow it.
- Add a feature test (`tests/Feature/DashboardTest.php`) asserting the Inertia response has the 5
  props and the feed item shape, so both refactors are guarded.

### Frontend

`resources/js/Pages/Dashboard.vue` becomes a thin composition root (~150 lines) that wires props to
child components. Proposed structure under `resources/js/Components/Dashboard/`:

| Component | Replaces section | Props |
| --- | --- | --- |
| `StateSelector.vue` | 1 | `modelValue`, emits `update` (state persisted via composable) |
| `CommunityFeed.vue` | 2 | `items` (wraps existing `CommentThread`) |
| `QuickActions.vue` | 3 | — |
| `FavoriteLakesGrid.vue` | 4 | `lakes` |
| `UpcomingTripsGrid.vue` | 5 | `trips` |
| `RecentReportsGrid.vue` | 6 | `reports` |
| `SafetyResources.vue` | 7 | `region`/`state` |
| `EmergencyBanner.vue` | 8 | — |

Composables under `resources/js/Composables/`:
- `useGeolocation.js` — browser geolocation + Nominatim reverse geocode → state abbreviation.
- `useStatePreference.js` — read/write selected state to localStorage.
- `useToast.js` — the toast message/type/timer logic currently inline.

### Hardcoded data → data module

Move the 19-state DNR/fishing/regulations map and the ice-thickness safety guide out of the template:
- `resources/js/data/stateResources.js` — export the `stateResources` object (MN, WI, MI, … AK).
- `resources/js/data/iceSafetyGuide.js` — thickness ranges + safe-activity copy.

`SafetyResources.vue` imports from these instead of defining them inline. (Optional future step: serve
state resources from the backend so they can be edited without a frontend deploy — out of scope here.)

---

## Navigation Rules (do not break Inertia)

The original stub flagged this explicitly. Constraints for every extracted component:

1. **Use `<Link>` for navigation**, never `<a href>` to internal routes and never `window.location`.
   Internal URLs come from Ziggy `route()` helpers (as the controller already provides via `url`).
2. **Use `router.post/visit`** (from `@inertiajs/vue3`) for feed actions (like / comment / acknowledge)
   with `{ preserveScroll: true, preserveState: true }` so the feed doesn't jump or remount.
3. **External links** (DNR, weather, social share) keep `target="_blank" rel="noopener"` and are the
   *only* place raw `<a href>` is allowed.
4. **Keep a single layout instance.** `AuthenticatedLayout` stays in `Dashboard.vue`, not pushed into
   children — wrapping persistent layouts inside child components breaks Inertia's persistent-layout
   optimization and causes remounts.
5. The `?comment=ID` highlight deep-link behavior must continue to work after extraction (it reads the
   query param on mount to scroll/highlight a feed comment).

---

## Suggested Sequence

1. **Backend (independent):** add `CommunityFeedService`, move feed methods, add feature test. Green.
2. **Data modules:** extract `stateResources.js` + `iceSafetyGuide.js`. Pure move, no behavior change.
3. **Composables:** extract `useGeolocation` / `useStatePreference` / `useToast`.
4. **Components, one at a time:** extract section-by-section, verifying the page renders + navigates
   after each. Start with the leaf sections (8, 3, 7) before the interactive feed (2).
5. **Thin the page:** once all sections are components, `Dashboard.vue` is just composition.
6. **Verify:** run `composer test`, `npm run build`, and click through login → dashboard → feed
   interactions → external links to confirm SPA navigation is intact.

---

## Out of Scope

- Changing the `/dashboard` URL, route name, or post-login redirect targets.
- Changing the feed prop shape or the feed-interaction API routes.
- Moving state resources to the database (noted as a possible future enhancement only).
