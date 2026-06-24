#!/bin/bash
set -euo pipefail

DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"

echo "=== vuno-leed Backend Setup ==="

echo "[1/3] Creating database and tables..."
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} < ../database/schema.sql

echo "[2/3] Testing PHP syntax..."
find . -name "*.php" -exec php -l {} \; | grep -v "No syntax"

echo "[3/3] Starting PHP dev server..."
echo ""
echo "  API running at: http://localhost:8000/api"
echo "  Ctrl+C to stop"
echo ""

php -S localhost:8000 -t public/
