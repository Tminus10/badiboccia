#!/usr/bin/env bash
# Self-contained, disposable MariaDB instance for local development only.
# Lives entirely inside dev/ - does not touch the system-wide Homebrew MariaDB install.
set -euo pipefail

DEV_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DATA_DIR="$DEV_DIR/mysql-data"
SOCKET="$DEV_DIR/mysql.sock"
PORT=3907
PIDFILE="$DEV_DIR/mysqld.pid"

MARIADBD="$(brew --prefix mariadb)/bin/mariadbd"
INSTALL_DB="$(brew --prefix mariadb)/bin/mariadb-install-db"
MYSQL_CLIENT="$(brew --prefix mariadb)/bin/mysql"

if [ ! -d "$DATA_DIR" ]; then
    echo "Initializing local dev database in $DATA_DIR ..."
    mkdir -p "$DATA_DIR"
    "$INSTALL_DB" --datadir="$DATA_DIR" --auth-root-authentication-method=normal >/dev/null
fi

if [ -f "$PIDFILE" ] && kill -0 "$(cat "$PIDFILE")" 2>/dev/null; then
    echo "Dev database already running (pid $(cat "$PIDFILE"))."
    exit 0
fi

echo "Starting dev MariaDB on socket $SOCKET (port $PORT) ..."
"$MARIADBD" \
    --datadir="$DATA_DIR" \
    --socket="$SOCKET" \
    --port="$PORT" \
    --pid-file="$PIDFILE" \
    --skip-networking=0 \
    --bind-address=127.0.0.1 \
    >"$DEV_DIR/mysqld.log" 2>&1 &

for i in $(seq 1 20); do
    if "$MYSQL_CLIENT" --socket="$SOCKET" -uroot -e "SELECT 1" >/dev/null 2>&1; then
        break
    fi
    sleep 0.5
done

"$MYSQL_CLIENT" --socket="$SOCKET" -uroot -e \
    "CREATE DATABASE IF NOT EXISTS badiboccia CHARACTER SET utf8mb4;
     CREATE USER IF NOT EXISTS 'badiboccia'@'localhost' IDENTIFIED BY 'badiboccia';
     GRANT ALL PRIVILEGES ON badiboccia.* TO 'badiboccia'@'localhost';
     FLUSH PRIVILEGES;"

"$MYSQL_CLIENT" --socket="$SOCKET" -uroot badiboccia < "$DEV_DIR/../migrations/001_init.sql"

echo "Dev database ready. Socket: $SOCKET  Port: $PORT  DB: badiboccia  User/Pass: badiboccia/badiboccia"
