# Year-Round Community Fishing — Roadmap

How Safe Ice Tool evolves from a winter ice-safety tool into a **season-aware, year-round
community fishing platform** (winter ice + spring/summer/fall open-water).

This is a **plan**. No code here has been applied yet. Decisions captured 2026-06-15.

---

## Vision & Decisions

- **Season model:** One app, season-aware. Same lakes and community; the *conditions* layer adapts
  by season. Winter shows ice conditions; open-water seasons show water conditions.
- **Community scope (all in):** catch reports, richer feed & social (follows / leaderboards / badges),
  local groups & regions, real-time crowd-sourced conditions.
- **Core data decision:** generalize `ice_reports` into a single season-aware **`condition_reports`**
  table — shared columns + a `season` discriminator + a JSON `attributes` bag for season-specific
  fields. (Not a parallel `water_reports` table; not one wide table of nullable columns.)

---

## Why the codebase is ready

| Layer | State | Notes |
| --- | --- | --- |
| Lakes | ✅ season-agnostic | pure location (lat/lng, name, state, county) |
| Trip posts / media / comments | ✅ season-agnostic | community content already generic |
| **Feed (reactions / comments / acknowledgements)** | ✅ **polymorphic** | keyed by `item_type` + `item_id` — new content types attach with NO migration |
| Notifications, favorites, profiles, @mentions, verifications | ✅ season-agnostic | reusable as-is |
| `ice_reports` | ❄️ ice-locked | thickness / ice_type / traffic_type / slush / cracks |
| `trips` safety fields | ❄️ ice-locked | min_thickness_inches / avoid_slush / avoid_pressure_cracks |
| `LakeSafetyService` | ❄️ ice-only | scores 0–100 from ice metrics only |
| Dashboard | ❄️ ice-themed | ice guide + fell-through-ice banner |

Greenfield: no catch/species tracking exists. `trips.target_species` is a freeform string today.

---

## Target Data Model

### `condition_reports` (evolved from `ice_reports`)
```
condition_reports
  id              bigint PK
  lake_id         FK → lakes (cascade)
  user_id         FK → users (nullable, nullOnDelete)
  lat, lng        decimal(10,7) nullable
  season          string  -- 'winter' | 'open_water'  (enum-backed; extensible)
  notes           text nullable
  attributes      json    -- season-specific payload (see below)
  -- moderation (kept from ice_reports)
  upvotes, downvotes  integer default 0
  is_flagged, is_hidden boolean default false
  created_at, updated_at
```

`attributes` JSON shape by season:
```
winter:     { thickness_inches, ice_type, traffic_type, has_slush, has_pressure_cracks }
open_water: { water_temp_f, clarity, wind_speed, wind_direction, wave_height, water_level, algae_bloom }
```

Validation per season lives in a Form Request (`StoreConditionReportRequest`) that switches its
rules on `season`. Define season-specific attribute keys as PHP enums/constants so they aren't
freeform (today `ice_type`/`traffic_type` are unvalidated strings).

**Migration strategy (no data loss):**
1. Create `condition_reports`.
2. Backfill: copy every `ice_reports` row → `condition_reports` with `season='winter'` and the five
   ice columns packed into `attributes`.
3. Point the app at `condition_reports`; keep `ice_reports` read-only for one release, then drop.
4. Keep route/name aliases (`/reports`) so existing links and the feed keep working.

### `fish_species` (new — catch foundation)
```
fish_species
  id, common_name, scientific_name (nullable),
  typical_season (nullable), slug, created_at, updated_at
```
Seeded with common North American freshwater species (walleye, northern pike, perch, crappie,
bluegill, bass, lake trout, etc.).

### `catches` (new — the headline community feature)
```
catches
  id              bigint PK
  user_id         FK → users
  trip_id         FK → trips (nullable)
  trip_post_id    FK → trip_posts (nullable)   -- catch can hang off a post for photos
  lake_id         FK → lakes (nullable)
  species_id      FK → fish_species (nullable) -- nullable so "other" is allowed
  length_inches   decimal nullable
  weight_lbs      decimal nullable
  bait            string nullable
  technique       string nullable
  caught_at       timestamp nullable
  released        boolean default false
  notes           text nullable
  created_at, updated_at
```
- `Trip hasMany Catches`; `User hasMany Catches`; `Catch belongsTo FishSpecies`.
- Catches become a **feed item type** (`item_type='catch'`) — instantly gets likes/comments/ack
  via the existing polymorphic feed. No new social plumbing.

### Trips — generalize safety prefs
- Add `season` (or `trip_type`) to `trips`.
- Replace ice-only prefs with a season-aware preferences JSON (or keep ice fields, add open-water
  fields), mirroring the `condition_reports` attributes approach for consistency.

---

## Safety scoring — pluggable per season

`LakeSafetyService` becomes a dispatcher over a `SafetyCalculator` interface:
```
SafetyCalculator (interface)
  ├─ IceSafetyCalculator      (current logic: thickness/slush/cracks → 0–100)
  └─ OpenWaterSafetyCalculator (new: water temp, wind, wave height, advisories)
```
The dashboard asks for "current safety for this lake in this season" and the right calculator runs.

---

## Phased Delivery

### Phase 0 — Guardrails (do first)
- Add factories (`IceReport`/`ConditionReport`, `Trip`, `Lake`) and a feature test for the
  dashboard + reports flow, so every later migration is guarded. Today only `UserFactory` exists.

### Phase 1 — Season-aware conditions
- `condition_reports` table + model + backfill from `ice_reports`.
- `StoreConditionReportRequest` with per-season rules; species/condition enums.
- Update report controllers + feed builders to read `condition_reports`.
- Season detection: auto by date (configurable per region) with a manual user toggle.

### Phase 2 — Catch reports (highest community value)
- `fish_species` + `catches` tables, models, seeder.
- Catch-logging UI (form/modal), attachable to a trip or post.
- Wire `catch` into the feed item types + notifications.

### Phase 3 — Season-aware dashboard
- Depends on the **[Dashboard refactor](DASHBOARD-PAGE-REFACTOR.md)** landing first (the page is
  1,134 lines; extract sections before making them season-conditional).
- Swap ice guide ⇄ open-water guide by season; season-appropriate safety banner; show catches.

### Phase 4 — Richer social
- User **follows** (followers / following) → personalized feed.
- **Leaderboards** (biggest catch by species, most active reporter) and **badges**.
- **Local groups / regions** — region-scoped boards and leaderboards (reuse `lakes.state`/`region`).
- **Real-time conditions** — surface latest `condition_reports` as a live "current conditions" panel.

### Phase 5 — Rebrand
- Broaden naming/marketing beyond "Safe Ice Tool" to reflect year-round scope. Keep `/dashboard`,
  route names, and share tokens stable to avoid breaking links.

---

## Sequencing notes & dependencies

- **Phase 0 before everything** — the migrations in Phase 1/2 are risky without test coverage.
- **Phase 3 needs the Dashboard refactor** — see `DASHBOARD-PAGE-REFACTOR.md`. Don't add season
  branching to a 1,134-line file; extract components first.
- Phases 1 and 2 are largely independent — catch reports (Phase 2) could ship first if you want the
  most visible community feature sooner; conditions (Phase 1) is the bigger architectural piece.
- Keep the polymorphic feed as the integration point for all new content types — it's the single
  biggest reason this pivot is low-friction.

---

## Out of scope (for now)

- Changing the polymorphic feed schema (it already supports new types).
- Real-time websockets/push (Phase 4 "real-time" = latest reports on load, not live sockets — that's
  a later, separate decision).
- Moving state DNR/safety resource data to the backend (tracked in the dashboard refactor doc).
