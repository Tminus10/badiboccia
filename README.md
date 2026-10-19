# BadiBoccia

A small web app for the summer beach-boccia league: group standings, a knockout
bracket, self-service result entry by teams (PIN login), an admin area, and a
read-only archive of past seasons. Plain PHP + MySQL/MariaDB, no framework, no
build step — designed to run on ordinary shared hosting (hostpoint.ch Standard/Smart).

## How it works

- **Anyone** can view standings, fixtures, and the bracket without logging in.
- **Teams** log in with a 4-digit PIN (tap your team, enter the PIN) and can then
  enter/correct the result of games *they* played, and add/change their own team
  photo on their team page.
- **Admins** log in with a username/password and manage seasons, groups, teams
  (incl. photos and PIN resets), the knockout bracket pairings, other admin
  accounts, and can correct any result. Every result change is written to an
  audit log.
- A season that isn't the *current* one is fully read-only (the Archive).
- Everything is available in **German** (Swiss spelling) and **English** – website,
  admin area, calendar feed, API messages and the iOS app. See [Languages](#languages).

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

## Languages

German is the source language: every user-facing text is written in German and wrapped in
`t('…')` (placeholders sprintf-style: `t('Gruppe %s', $name)`), and `lang/en.php` maps those
German texts to English. That is the same model as the iOS app's string catalog, so a text has
the same German key on both platforms.

- The language comes from `?lang=de|en` (remembered in a cookie; the footer and the
  navigation link to it), else the browser's `Accept-Language`, else German. The API answers
  in the request's `Accept-Language`, so the app gets messages in its own language; the app
  opens website pages with `?lang=` in its language. The calendar feed URL carries the
  language of the page it was copied from.
- The rules page is one template per language: `src/Views/rules/<code>.php`.
- Shared terms (Saison/Season, Turnierbaum/Bracket, Spiel/Game …) are fixed in
  `lang/glossary.json`. After changing any text, run

  ```
  php dev/check-translations.php
  ```

  It reports missing or unused translations, mismatched placeholders, "ß", and texts that
  don't use the glossary terms – for the website and for the app's
  `../badiboccia-ios/BocciaScore/Resources/Localizable.xcstrings`.
- **Adding a language** (e.g. French): add it to `I18n::LANGUAGES`, copy `lang/en.php` to
  `lang/fr.php` and translate it, add `src/Views/rules/fr.php`, add a `fr` column to
  `lang/glossary.json`, and add the language to the app's string catalog (Xcode) and its
  `CFBundleLocalizations`. The check script then lists everything still missing.

## JSON API (for the iOS/iPadOS app)

`/api/v1` serves the native app (separate repo: `../badiboccia-ios`). Reading
is public, like the website; writing needs a team token from the PIN login and follows the same
rules as the web forms (only the two teams playing, only in the current season, same score
validation, audit log, schedule-clash check). Team PINs are never part of any response.

| Method | Path | |
|---|---|---|
| GET  | `/api/v1` | API version, `env` (dev/prod), current season id |
| GET  | `/api/v1/seasons` | all seasons |
| GET  | `/api/v1/seasons/current`, `/api/v1/seasons/{id}` | one season: teams, groups with standings and games, bracket (empty slots carry structured placeholders), podium, calendar entries |
| GET  | `/api/v1/teams/{id}` | team, its seasons, all its games, its opponents |
| POST | `/api/v1/auth/login` | `{"team_id", "pin", "device"}` → `{"token", "team"}`; same lockout as the web login |
| POST | `/api/v1/auth/logout` | revokes the token |
| GET  | `/api/v1/me` | the token's team (401 once revoked) |
| POST | `/api/v1/games/{id}/result` | `{"sets_a", "sets_b", "played_date", "game_time"}` – omit date/time to keep them, `null` clears |
| POST | `/api/v1/games/{id}/schedule` | `{"played_date", "game_time", "force"}` – 409 with the clashing games unless `force` |
| POST | `/api/v1/teams/{id}/photo` | multipart field `photo` (JPG/PNG/WEBP, max. 8 MB) → `{"team"}`; own team only |

Send the token as `Authorization: Bearer <token>` (or `X-Api-Token: <token>`, for hosts that drop
the Authorization header). Errors are `{"error": "<code>", "message": "<text>"}`, the message in
the request's `Accept-Language` (German or English).
Tokens live in `api_tokens` (SHA-256 hashes only, see `migrations/004_api_tokens.sql`);
resetting a team's PIN in the admin area logs that team out of the app everywhere.

## Notes on the domain rules

- Group phase: single round-robin (every team plays every other team in its
  group once), games to 15 points, best-of-3 sets. Standings points: 2:0 win = 3,
  2:1 win = 2, loss = 1.
- Top 2 of each group advance to a single-elimination knockout (QF → SF → Final),
  games to 21 points. The admin manually assigns the 8 qualifiers into the QF
  slots (no fixed seeding formula existed on the old paper sheets); semifinal and
  final slots then fill in automatically as winners are recorded.
