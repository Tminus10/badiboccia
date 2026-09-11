# BadiBoccia

A small web app for the summer beach-boccia league: group standings, a knockout
bracket, self-service result entry by teams (PIN login), an admin area, and a
read-only archive of past seasons. Plain PHP + MySQL/MariaDB, no framework, no
build step — designed to run on ordinary shared hosting (hostpoint.ch Standard/Smart).

## How it works

- **Anyone** can view standings, fixtures, and the bracket without logging in.
- **Teams** log in with a 4-digit PIN (tap your team, enter the PIN) and can then
  enter/correct the result of games *they* played.
- **Admins** log in with a username/password and manage seasons, groups, teams
  (incl. photos and PIN resets), the knockout bracket pairings, other admin
  accounts, and can correct any result. Every result change is written to an
  audit log.
- A season that isn't the *current* one is fully read-only (the Archive).

## Requirements

- PHP 8.1+ with `pdo_mysql` and `gd` extensions (both standard on hostpoint).
- MySQL / MariaDB.
- No Composer packages, no Node.js, no build step — just PHP files.

## Local development

Requires `php` and `mariadb` (`brew install php mariadb` on macOS). The dev
setup runs a throwaway MariaDB instance entirely inside `dev/` — it does not
touch any system-wide database.

```
./dev/serve.sh
```

This initializes the local dev DB (first run only), applies `migrations/001_init.sql`,
and serves the app at http://localhost:8090.

Since a fresh database has no admin account yet, create the first one from the
command line:

```
php scripts/create-admin.php <username> <password> "<display name>"
```

Then visit `/admin/login` to sign in and set up a season, groups, and teams.

## Deployment (hostpoint.ch)

One-time server setup:

1. In the hostpoint control panel, create a MySQL/MariaDB database.
2. Import the schema: open phpMyAdmin and run `migrations/001_init.sql`, or via SSH:
   `mysql -u <user> -p <database> < migrations/001_init.sql`.
3. Copy `config/config.example.php` to `config/config.php` on the server and fill
   in the real DB credentials. This file is intentionally excluded from the repo
   and from deploys (it holds secrets and differs per environment).
4. Point the `boccia.reutener.swiss` (sub)domain's document root at this project's
   `public/` folder.
5. Over SSH, run `php scripts/create-admin.php <username> <password> "<display name>"`
   to create the first admin account.

Every deploy after that:

```
export HOSTPOINT_SSH=myuser@myuser.hostpoint.ch
export HOSTPOINT_PATH=/home/myuser/boccia.reutener.swiss
./scripts/deploy.sh
```

This rsyncs the project over SSH, skipping `config/config.php` (server-only secrets)
and `public/uploads/` (live team photos) so neither gets clobbered.

If SSH isn't available on your plan, upload the same files with any SFTP client
instead (e.g. Cyberduck, FileZilla) — just skip `config/config.php` if it already
exists on the server, and skip `public/uploads/`.

## Notes on the domain rules

- Group phase: single round-robin (every team plays every other team in its
  group once), games to 15 points, best-of-3 sets. Standings points: 2:0 win = 3,
  2:1 win = 2, loss = 1.
- Top 2 of each group advance to a single-elimination knockout (QF → SF → Final),
  games to 21 points. The admin manually assigns the 8 qualifiers into the QF
  slots (no fixed seeding formula existed on the old paper sheets); semifinal and
  final slots then fill in automatically as winners are recorded.
