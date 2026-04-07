# Operative Memory - 2026-04-07

This document records the operational mode used today for this Laravel project, and becomes a reusable reference for future requests.

## 1) Operating objective

Work with analysis-first execution, preserve backward compatibility, and avoid regressions across client, waiter, kiosk, and admin flows.

## 2) Repository map used today

- Core backend: `app/`
- Main routes: `routes/web.php`
- Views: `resources/views/`
- Runtime frontend scripts: `public/*.js` and `public/js/*.js`
- Migrations: `database/migrations/`
- Memory and guides: `AGENTS.md`, `docs/AI_PROJECT_GUIDE.md`, module memories in `docs/*.md`
- Deploy profile: `deploy/SITEGROUND_PUBLISH.md`
- Waiter APK template: `android-wrapper-template/`

## 3) Daily execution protocol (applied)

1. Read project memory first (`AGENTS.md`, `docs/AI_PROJECT_GUIDE.md`, module-specific memory when applicable).
2. Validate real route and middleware in `routes/web.php`.
3. Trace implementation chain:
   - controller validations
   - services used
   - schema guards (`Schema::hasTable/hasColumn`)
   - DB contract
4. Trace frontend contract:
   - blade view that renders the flow
   - matching runtime script (`public/*.js` or `public/js/*.js`)
5. Keep migration safety:
   - preserve automatic migration behavior on first module access when schema changes are required.
6. Preserve compatibility:
   - avoid breaking legacy aliases and established request/response contracts.
7. Validate non-regression:
   - end-to-end path (route -> validation -> service -> persistence -> response)
   - affected UI flow
   - relevant tests in `tests/Feature` and `tests/Unit`

## 4) Key operational hotspots identified today

1. Migration execution is coupled to request paths (`AppServiceProvider` + `AutoMigrationRunner` + module-level migration triggers).
2. Multiple controllers/services depend on partial-schema guards (`Schema::hasTable/hasColumn`), so behavior can vary by tenant DB state.
3. Legacy compatibility routes and POST upsert-compatible behavior in `routes/web.php` require careful preservation.
4. Highlights logic includes table-name case fallback (`Highlights` vs `highlights`).
5. Catalog integration is tightly coupled to `ProductController::index` response contract.
6. Waiter branch-access fallback behavior can widen access when `user_branches` does not exist.
7. CSRF exception list is broad in `bootstrap/app.php`, so endpoint changes must be reviewed carefully.

## 5) Practical start checklist for future requests

1. Memory read: `AGENTS.md` + `docs/AI_PROJECT_GUIDE.md` + module memory.
2. Route-first confirmation in `routes/web.php`.
3. Controller/service/schema inspection before coding.
4. Blade + JS pair update when behavior changes.
5. Migration auto-trigger safety if new migration is introduced.
6. Compatibility and non-regression validation with tests.

## 6) Notes

- This memory is operational and can be superseded by newer dated snapshots.
- Keep this document focused on execution methodology; functional domain truth remains in `docs/AI_PROJECT_GUIDE.md` and specialized module memories.
