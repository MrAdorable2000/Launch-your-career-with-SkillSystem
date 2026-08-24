# SkillSystem — Complete UI/UX Redesign Plan

## Architecture Preserved (NO changes)
- **Front controller** (`index.php`): PSR-4 autoloading, .env parsing, session, CSRF init
- **Router**: regex-based, URL params passed positionally to controller methods
- **BaseController**: `view()` method extracts `$data` + injects shared vars (`$isLoggedIn`, `$userName`, `$userRole`, `$userId`, `$userAvatar`, `$flashMessage`, `$csrfField`, `$unreadNotifications`, `$unreadMessages`, `$recentNotifications`)
- **All 8 controllers**: method signatures, `$data` keys, `$_POST` field names — UNCHANGED
- **All 17 models**: UNCHANGED
- **All 10 helpers**: UNCHANGED (Session, CSRF, Flash, URL, Validator, Upload, functions, AiScorer, Theme, Component)
- **Routes**: UNCHANGED (70 routes)
- **Database**: UNCHANGED (46 tables)

## What Changes

### 1. ONE CSS Design System (`public/assets/css/app.css`)
**Strategy**: Use `.ss-*` namespace exclusively. Do NOT use Bootstrap's `.btn` classes — use `.ss-btn` instead to avoid all specificity wars.

**Color palette** (user-specified):
- Primary: #2563EB (blue)
- Secondary: #4F46E5 (indigo)
- Accent: #06B6D4 (cyan)
- Success: #10B981, Warning: #F59E0B, Danger: #EF4444
- Background: #F8FAFC, Surface: #FFFFFF
- Text: #0F172A (primary), #475569 (secondary), #94A3B8 (muted)

**Dark mode**: `[data-theme="dark"]` on `<html>`, all colors have dark variants.

**Components**: ss-btn (6 variants), ss-card, ss-stat-card, ss-badge, ss-chip, ss-avatar, ss-progress, ss-input, ss-float, ss-table, ss-alert, ss-modal, ss-tabs, ss-timeline, ss-empty, ss-skeleton, ss-sidebar, ss-topbar, ss-hero, ss-footer, etc.

### 2. ONE JS Engine (`public/assets/js/app.js`)
- Theme manager (cookie + localStorage)
- Sidebar toggle/collapse
- AJAX notification polling
- Toast system
- Form validation
- Password strength meter
- File upload preview
- Table enhancements (sort/search/export)
- Animated counters
- Scroll reveal
- Chart.js defaults

### 3. ONE Shared App Layout (`layouts/app.php`)
- Fixed sidebar (dark, collapsible, role-based menu)
- Sticky glassmorphism topbar (search, theme toggle, notifications, messages, profile)
- Content area
- Used by ALL authenticated pages (student, employer, admin, university, mentor)

### 4. ONE Auth Layout (`layouts/auth.php`)
- Two-panel split: gradient brand panel + form panel
- Used by login, register, forgot-password
- **FIX**: Update `BaseController::getLayoutPath()` to return `auth.php` for `auth/*` views

### 5. ONE Landing Layout (`layouts/landing.php`)
- Sticky navbar + footer
- Used by homepage and public pages

### 6. Reusable Component Helper (`app/Helpers/Component.php`)
- `button()`, `statCard()`, `card()`, `badge()`, `avatar()`, `progress()`, `alert()`, `emptyState()`, `table()`, `formField()`, `modal()`, `tabs()`, `pageHeader()`, `timelineItem()`, `skeleton()`

### 7. All 46 Views Redesigned
Each view:
- Starts with `<?= Component::pageHeader(...) ?>`
- Uses `.ss-card`, `.ss-stat-card`, `.ss-table`, `.ss-btn`, `.ss-input`, `.ss-float` etc.
- Includes `<?= $csrfField ?>` in every form
- Uses exact field names from controller analysis
- Chart.js for dashboards
- Responsive (mobile-first)
- Dark mode compatible

## Execution Order
1. CSS design system (foundation)
2. JS engine
3. Component helper
4. Fix BaseController (auth layout routing + pageTitle)
5. 3 layouts (app, auth, landing)
6. Homepage
7. Auth pages (3)
8. Student views (20) — via subagent
9. Employer views (8) — via subagent
10. Admin views (6) — via subagent
11. University views (3) + Mentor views (2) — via subagent
12. Verify + package
