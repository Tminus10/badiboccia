#!/usr/bin/env bash
# Deploys BadiBoccia to hostpoint.ch over SSH/rsync, to either the PROD or DEV
# subdomain. Both live under the same hostpoint account, so only the target
# path differs.
#
# Usage:
#   ./scripts/deploy.sh prod
#   ./scripts/deploy.sh dev
#
# Override the target explicitly if your local setup differs from the defaults:
#   export HOSTPOINT_SSH=myuser@myuser.hostpoint.ch
#   export HOSTPOINT_PATH=/home/myuser/boccia.reutener.swiss
#   ./scripts/deploy.sh prod
#
# Defaults to the `hostpoint-badiboccia` alias from ~/.ssh/config (key-based
# auth). Using the raw hostname instead skips that config block and falls
# back to password auth, since ssh only applies a Host block when you pass
# its alias verbatim -- not when you pass the hostname it resolves to.
set -euo pipefail

ENV_NAME="${1:-}"
if [[ "$ENV_NAME" != "prod" && "$ENV_NAME" != "dev" ]]; then
  echo "Usage: $0 prod|dev" >&2
  exit 1
fi

HOSTPOINT_SSH="${HOSTPOINT_SSH:-hostpoint-badiboccia}"
if [[ "$ENV_NAME" == "prod" ]]; then
  HOSTPOINT_PATH="${HOSTPOINT_PATH:-/home/reutener/www/boccia.reutener.swiss}"
else
  HOSTPOINT_PATH="${HOSTPOINT_PATH:-/home/reutener/www/dev.boccia.reutener.swiss}"
fi

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "Deploying to $ENV_NAME: $HOSTPOINT_SSH:$HOSTPOINT_PATH"

rsync -avz --delete \
  --exclude '.git' \
  --exclude '.claude' \
  --exclude 'dev' \
  --exclude 'config/config.php' \
  --exclude 'public/uploads' \
  "$ROOT_DIR/" "$HOSTPOINT_SSH:$HOSTPOINT_PATH/"

cat <<EOF

Done.

Note: config/config.php and public/uploads/ are intentionally excluded from sync:
  - config/config.php must already exist on the server with that environment's own
    DB credentials (copy config/config.example.php there once and edit it by hand;
    set app.env to '$ENV_NAME').
  - public/uploads/ holds live team photos and must not be wiped by a deploy.

First-time server setup (only needed once per environment):
  1. Create the MySQL/MariaDB database in the hostpoint control panel.
  2. Import migrations/001_init.sql into it (phpMyAdmin, or \`mysql < migrations/001_init.sql\` over SSH).
  3. Copy config/config.example.php to config/config.php on the server, fill in the DB
     credentials, and set app.env to '$ENV_NAME'.
  4. Run \`php scripts/create-admin.php <username> <password> "<display name>"\` over SSH to create the first admin.
EOF
