#!/usr/bin/env bash
# Runs the app locally with PHP's built-in server against the dev database.
set -euo pipefail

DEV_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(dirname "$DEV_DIR")"

"$DEV_DIR/start-db.sh"

cp "$DEV_DIR/config.php" "$ROOT_DIR/config/config.php"

echo "Serving BadiBoccia at http://localhost:8090 (Ctrl+C to stop)"
php -S localhost:8090 -t "$ROOT_DIR/public" "$DEV_DIR/router.php"
