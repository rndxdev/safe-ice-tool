# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Safe Ice Tool is a Laravel 12 + Vue 3 + Inertia.js full-stack web application for ice fishing enthusiasts to share ice condition reports and safely plan trips. It's a community-driven platform for tracking lake ice safety.

**Stack:** PHP 8.2+, Laravel 12, Vue 3, Inertia.js, Tailwind CSS, Vite, PostgreSQL/SQLite

## Common Commands

```bash
# First-time setup
composer setup

# Development (runs server, queue, logs, and Vite concurrently)
composer dev

# Run tests
composer test

# Build frontend for production
npm run build

# Run Vite dev server only
npm run dev
```

## Architecture

### Backend Structure

- **Controllers** (`app/Http/Controllers/`) - Route handlers following Laravel resource conventions
- **Models** (`app/Models/`) - Eloquent models: User, Lake, IceReport, Trip, TripPost, TripPostComment, TripPostMedia
- **Services** (`app/Services/`) - Business logic extracted from controllers
  - `LakeSafetyService` - Core algorithm that calculates lake safety scores (0-100) based on ice report data from the last 10 days
  - `ReverseGeoCodeService` - US Census reverse geocoding for lake coordinates
- **Form Requests** (`app/Http/Requests/`) - Validation classes

### Frontend Structure

- **Pages** (`resources/js/Pages/`) - Inertia page components organized by feature: Auth, Lakes, Trips, Posts, Reports
- **Layouts** (`resources/js/Layouts/`) - AuthenticatedLayout, GuestLayout, MarketingLayout
- **Components** (`resources/js/Components/`) - Reusable Vue UI components

### Data Model

- **Lakes** have many IceReports, can be favorited by Users (pending/approved status)
- **IceReports** contain thickness, ice_type, traffic_type, has_slush, has_pressure_cracks; include voting system for moderation (auto-hidden at -5 downvotes)
- **Trips** are planned fishing trips with shareable public links via token
- **TripPosts** are community content with media attachments and comments

### Key Routes

Public (unauthenticated):
- `/t/{token}` - Public trip share
- `/p/{token}` - Public post share

Authenticated routes require `auth:verified` middleware. Main resources: `/lakes`, `/trips`, `/community`, `/reports`.

## Testing

Tests use SQLite in-memory database. Run with `composer test` which clears config cache first.

## Development Environment

- Default: SQLite with file-based sessions
- Docker: PostgreSQL 16 available via `docker-compose.yml` (port 5432)

## Path Aliases

- JavaScript: `@/*` maps to `resources/js/*`
- Ziggy provides route helpers for JavaScript
