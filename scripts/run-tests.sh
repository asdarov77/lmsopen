#!/usr/bin/env bash
set -e

# 1. Fresh DB with seed
echo "🔹 Migrating and seeding database"
php artisan migrate:fresh --seed

# 2. Backend unit & feature tests

echo "🔹 Running PHP unit tests"
php artisan test

# 3. Frontend unit tests (Vitest/Jest) – if configured
if [ -f package.json ]; then
  echo "🔹 Running frontend unit tests"
  npm run test:unit || true
fi

# 4. Playwright E2E tests (if installed)
if [ -d playwright ]; then
  echo "🔹 Running Playwright E2E tests"
  npx playwright test || true
fi

# 5. Security checks
if command -v phpstan >/dev/null; then
  echo "🔹 Running PHPStan static analysis"
  phpstan analyse -c phpstan.neon || true
fi
if command -v composer >/dev/null; then
  echo "🔹 Running Composer audit"
  composer audit || true
fi

# 6. Quality report summary
if [ -f storage/logs/laravel.log ]; then
  echo "🔹 Recent Laravel logs (last 20 lines)"
  tail -n 20 storage/logs/laravel.log || true
fi

echo "✅ All test steps completed"
