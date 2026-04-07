# Agent Instructions

## SiteGround Publish Memory

When a user asks to publish/deploy this project, do this first:

1. Read `deploy/SITEGROUND_PUBLISH.md`.
2. Load variables from `.publish.env` if the file exists.
3. Never print or commit secrets from `.publish.env`.

If `.publish.env` does not exist, ask for the missing credentials and then continue.

### Best confirmed method (SiteGround)

Use this as the default deploy recipe for this project:

1. Build a production package in a temporary directory:
   - `composer install --no-dev --prefer-dist --optimize-autoloader`
   - exclude local-only/secrets (`.env`, `.publish.env`, tests, backups, local artifacts)
2. Prepare `public_html` layout:
   - flatten `public/*` into package root
   - patch `index.php` to use:
     - `__DIR__.'/vendor/autoload.php'`
     - `__DIR__.'/bootstrap/app.php'`
3. Upload one ZIP to `FTP_REMOTE_DIR`.
4. Extract server-side with a temporary token-protected extractor script.
5. Remove deploy artifacts (`ZIP` and extractor script).
6. Verify at least:
   - root URL resolves successfully (`200` final after redirects)
   - `/login` returns `200`
   - `/install` may return `403` on already-installed production (acceptable).

## Migration Memory

For this Laravel version, the user requires automatic migrations when entering a module for the first time and there are pending migrations.

Rules:

1. When a change introduces new migrations, include an automatic migration trigger on first module access (with lock/safety checks).
2. Do not leave migration execution only as a manual admin action when module access depends on pending schema changes.
3. Keep backward compatibility and avoid exposing raw migration errors to end users.

## APK Meseros Memory

Project context to always keep when user mentions "APK de meseros" or "app de meseros":

1. Laravel orchestrates APK builds via GitHub Actions (external Android wrapper repo), not in SiteGround.
2. Existing endpoints and flow:
   - `POST /settings/waiter-apk/config`
   - `POST /settings/waiter-apk/generate`
   - `GET /settings/waiter-apk/status`
   - `POST /integrations/waiter-apk/callback` (HMAC signature required).
3. Required Laravel env vars:
   - `WAITER_APK_GH_OWNER`
   - `WAITER_APK_GH_REPO`
   - `WAITER_APK_GH_WORKFLOW`
   - `WAITER_APK_GH_REF`
   - `WAITER_APK_GH_TOKEN`
   - `WAITER_APK_GH_API_BASE`
   - `WAITER_APK_CALLBACK_SECRET`
4. Android wrapper template is inside `android-wrapper-template/` and includes workflow + launcher icons.
5. User requested beginner-friendly, step-by-step guidance for GitHub Actions every time this topic appears.

## Combo Manager Memory

When the user mentions combos/combo manager in this project:

1. Read `docs/COMBOS_MEMORY.md` first.
2. Treat that file as the canonical baseline of agreed behavior (cliente + kiosko, backend validation, pricing, persistence, compatibility).
3. Keep non-combo flows unchanged and preserve backward compatibility when implementing new combo changes.

## Analysis-First Convention (Always On)

When the user requests any change and the implementation path is not 100% clear:

1. Analyze the affected module first before coding.
2. Start with memory/docs (`AGENTS.md`, `docs/AI_PROJECT_GUIDE.md`, relevant `docs/*.md`) and then verify real routes in `routes/web.php`.
3. Inspect controller validations, services, schema guards (`Schema::hasTable/hasColumn`), and related frontend files (`resources/views`, `public/js`).
4. Preserve existing module flows and backward compatibility while implementing.
5. Do not skip analysis in uncertain cases; use this convention as mandatory project behavior.
6. If uncertainty persists, follow the quick protocol from `docs/AI_PROJECT_GUIDE.md` section "Protocolo de análisis por solicitud (plantilla rápida)" before any code change.

## Modus Operandi Memory (Updated 2026-04-07)

Operational flow to keep as project memory from now on:

1. Start every non-trivial request with repository orientation:
   - Read `AGENTS.md` and `docs/AI_PROJECT_GUIDE.md`.
   - If request is module-specific, read the corresponding memory file first (`docs/COMBOS_MEMORY.md`, `docs/WAITER_APK_INTEGRATION.md`, etc.).
2. Confirm real routing before coding:
   - Locate exact endpoint/page in `routes/web.php`.
   - Confirm middleware and compatibility aliases before changing behavior.
3. Trace backend chain in this order:
   - Controller validations
   - Service logic
   - `Schema::hasTable/hasColumn` guards
   - DB writes/reads
4. Trace frontend contract for the same flow:
   - Blade in `resources/views/*`
   - Runtime script pair in `public/*.js` or `public/js/*.js`
5. Respect migration safety:
   - If schema changes are introduced, preserve first-access auto-migration behavior with lock/safe execution.
6. Prioritize backward compatibility checks around known hotspots:
   - Legacy route aliases and upsert-compatible POSTs in `routes/web.php`
   - Highlight table case fallback (`Highlights` vs `highlights`)
   - Catalog integration coupling to `ProductController::index`
   - Waiter branch access fallback behavior
7. Close work with explicit non-regression verification:
   - Route -> validation -> service -> persistence -> response
   - Affected client/waiter/kiosk/admin UI flow
   - Existing feature tests for the touched module

Reference detail for this operating snapshot: `docs/OPERATIVE_MEMORY_2026-04-07.md`.
