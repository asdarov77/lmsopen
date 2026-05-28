# Testing Guide

## Prerequisites
- **PHP 8.2+**, Composer
- **Node 18+**, npm
- **PostgreSQL** (local instance)
- Optional: **Playwright** (`npm i -D @playwright/test`)

## Setup
```bash
# clone repo and cd into it
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm ci
```

## Running the full suite
```bash
./scripts/run-tests.sh
```
The script performs:
1. Fresh DB migration + seeding
2. All Laravel/PHPUnit tests
3. Front‑end unit tests (if `npm run test:unit` exists)
4. Playwright E2E tests (if `playwright/` folder exists)
5. Static analysis (PHPStan) and Composer audit
6. Summarises recent Laravel logs.

## Individual test types
- **PHPUnit unit / feature**: `php artisan test --testsuite=Unit` or `Feature`
- **Frontend unit**: `npm run test:unit`
- **Playwright E2E**: `npx playwright test`
- **Visual regression**: add `--project=visual` flag to Playwright command.
- **Load testing**: place k6 scripts under `scripts/` and run `k6 run scripts/load-test.js`.
- **Security**: `phpstan analyse -c phpstan.neon` and `composer audit`.
- **Migration integrity**: `php artisan migrate:fresh && php artisan migrate:rollback` inside a test.

## Flaky test handling
If a test fails intermittently, run:
```bash
./scripts/run-flaky.sh   # retries up to 3 times
```
A failing flaky run will exit with non‑zero code for CI‑like manual checks.

## Adding new tests
1. **Backend** – `tests/Unit` for pure PHP logic, `tests/Feature` for DB‑aware tests.
2. **Frontend** – Vue component tests under `resources/js/components/__tests__` using Vitest/Jest.
3. **E2E** – new files in `playwright/e2e/*.spec.ts`.
4. **Visual** – snapshots live in `playwright/visual/__snapshots__`.

## Load & Stress Testing
- Install **k6** (`brew install k6` or download binary) and place scripts in `scripts/load/`.
- Run: `k6 run scripts/load/api_load.js` to hit critical API endpoints.
- Adjust VUs and duration in script header.

## Security Testing
- Static analysis: `phpstan analyse -c phpstan.neon`.
- Dependency audit: `composer audit` (or `npm audit` for frontend).
- Dynamic scan: run OWASP ZAP in daemon mode and execute `zap-cli quick-scan http://localhost:8000`.

## Migration & DB Integrity Tests
- In `tests/Feature/MigrationTest.php` verify `php artisan migrate:fresh --seed` runs without errors.
- After migrations, assert presence of critical tables, constraints, and sample data counts.
- Include rollback tests.

## API Contract Tests
- Use JSON schema files under `tests/schemas/`.
- In `tests/Feature/ApiContractTest.php` send requests and validate responses against schemas using `league/json-guard`.
- Optionally integrate Pact provider verification.

## Flaky Test Management
- All flaky tests are automatically retried by `./scripts/run-flaky.sh` (up to 3 attempts).
- Flake logs are stored in `storage/logs/flaky.log` for analysis.
- Regularly review and fix underlying nondeterminism.

## Test Monitoring & Metrics
- Generate coverage report: `php artisan test --coverage-html=coverage`.
- Store JUnit XML: `php artisan test --log-junit=reports/junit.xml`.
- Use `phpmetrics` or `infection` for mutation scores; run via `./scripts/run-mutation.sh`.
- Periodic review of `reports/` directory for trends.

## Guidelines
- Keep tests deterministic: freeze time (`Carbon::setTestNow`) and mock external services.
- Aim for **≥ 80 %** coverage of critical backend code (see `coverage/index.html`).
- Review visual diffs manually before merging.
- Document any new npm scripts in this file.

- Keep tests deterministic: freeze time (`Carbon::setTestNow`) and mock external services.
- Aim for **≥ 80 %** coverage of critical backend code (see `coverage/index.html`).
- Review visual diffs manually before merging.
- Document any new npm scripts in this file.
