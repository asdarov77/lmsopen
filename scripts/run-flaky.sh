#!/usr/bin/env bash
set -e
MAX_RETRIES=3
CMD="php artisan test"
for i in $(seq 1 $MAX_RETRIES); do
  echo "Run #$i"
  if $CMD; then
    echo "✅ Tests passed"
    exit 0
  fi
  echo "⚠️ Failed – retrying"
done

echo "❌ Tests still failing after $MAX_RETRIES attempts"
exit 1
