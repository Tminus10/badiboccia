#!/usr/bin/env bash
# Deploys BadiBoccia to hostpoint.ch over SSH/rsync.
# Usage:
#   export HOSTPOINT_SSH=myuser@myuser.hostpoint.ch
#   export HOSTPOINT_PATH=/home/myuser/boccia.reutener.swiss
#   ./scripts/deploy.sh
set -euo pipefail

: "${HOSTPOINT_SSH:?Set HOSTPOINT_SSH, e.g. export HOSTPOINT_SSH=myuser@myuser.hostpoint.ch}"
: "${HOSTPOINT_PATH:?Set HOSTPOINT_PATH, e.g. export HOSTPOINT_PATH=/home/myuser/boccia.reutener.swiss}"

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

rsync -avz --delete \
  --exclude '.git' \
  --exclude 'dev' \
  --exclude 'config/config.php' \
  --exclude 'public/uploads' \
  "$ROOT_DIR/" "$HOSTPOINT_SSH:$HOSTPOINT_PATH/"

cat <<'EOF'

Done.

Note: config/config.php and public/uploads/ are intentionally excluded from sync:
  - config/config.php must already exist on the server with the production DB
    credentials (copy config/config.example.php there once and edit it by hand).
  - public/uploads/ holds live team photos and must not be wiped by a deploy.

First-time server setup (only needed once):
  1. Create the MySQL/MariaDB database in the hostpoint control panel.
  2. Import migrations/001_init.sql into it (phpMyAdmin, or `mysql < migrations/001_init.sql` over SSH).
  3. Copy config/config.example.php to config/config.php on the server and fill in the DB credentials.
  4. Run `php scripts/create-admin.php <username> <password> "<display name>"` over SSH to create the first admin.
EOF
