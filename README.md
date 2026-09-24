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

Live at **https://boccia.reutener.swiss**, deployed to hostpoint's Standard plan.

Server layout: hostpoint requires the document root to live inside `~/www/`, so
the whole project is deployed to `/home/reutener/www/boccia.reutener.swiss/`
with the subdomain's document root pointed at the `public/` subfolder within
it (set via the hostpoint subdomain wizard: `boccia.reutener.swiss/public`).
That keeps `config/`, `src/`, and `migrations/` next to `public/` but outside
the web-reachable root.

One-time server setup (already done for this deployment; repeat only for a
fresh environment):

1. In the hostpoint control panel, create a MySQL/MariaDB database.
2. Import the schema: open phpMyAdmin and run `migrations/001_init.sql`, or via SSH:
   `mysql -h <db-internal-host> -u <user> -p <database> < migrations/001_init.sql`.
3. Copy `config/config.example.php` to `config/config.php` on the server and fill
   in the real DB credentials (use the **internal** DB host, e.g.
   `reutener.mysql.db.internal` — the external host is only for connecting from
   your own computer). This file is intentionally excluded from the repo and
   from deploys (it holds secrets and differs per environment).
4. Enable SSH under the hostpoint control panel's Advanced → SSH-Zugang, add a
   public key there, and connect as `<user>@<user>.ssh.cloud.hostpoint.ch`.
5. Over SSH, run `php scripts/create-admin.php <username> <password> "<display name>"`
   to create the first admin account.

Every deploy after that:

```
./scripts/deploy.sh prod
```

(Locally, `~/.ssh/config` has a `hostpoint-badiboccia` alias set up for this;
`scripts/deploy.sh` defaults `HOSTPOINT_SSH`/`HOSTPOINT_PATH` for both `prod`
and `dev`, so no env vars are needed unless your setup differs — see the
comments at the top of the script.)

This rsyncs the project over SSH, skipping `config/config.php` (server-only secrets)
and `public/uploads/` (live team photos) so neither gets clobbered.

If SSH isn't available on your plan, upload the same files with any SFTP client
instead (e.g. Cyberduck, FileZilla) — just skip `config/config.php` if it already
exists on the server, and skip `public/uploads/`.

### DEV instance

There's also an online DEV instance at **https://dev.boccia.reutener.swiss**,
used to develop and test against before anything reaches PROD. It's a second
subdomain under the same hostpoint account, pointed at its own `public/`
document root the same way PROD is, with its own MySQL database and its own
`config/config.php` (same steps as "One-time server setup" above, using
`dev.boccia.reutener.swiss` as the path and setting `app.env` to `'dev'` —
that's what makes the `[DEV]` banner show on every page, so the two
environments can never be mistaken for each other). Because it's a separate
document root and database, PROD's data and deploys are completely
unaffected by whatever happens on DEV.

Deploy to it with:

```
./scripts/deploy.sh dev
```

## Notes on the domain rules

- Group phase: single round-robin (every team plays every other team in its
  group once), games to 15 points, best-of-3 sets. Standings points: 2:0 win = 3,
  2:1 win = 2, loss = 1.
- Top 2 of each group advance to a single-elimination knockout (QF → SF → Final),
  games to 21 points. The admin manually assigns the 8 qualifiers into the QF
  slots (no fixed seeding formula existed on the old paper sheets); semifinal and
  final slots then fill in automatically as winners are recorded.
