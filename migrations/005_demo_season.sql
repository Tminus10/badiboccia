-- The demo season: a playable sandbox on the live site for the App Store review, App Store
-- screenshots and anyone who wants to try the app, with its own teams and a public PIN.
-- Demo seasons and demo teams are hidden everywhere in the league (season list, archive,
-- calendar, admin team list); scripts/seed-demo.php creates and nightly resets them.
-- Run once against an existing database that was set up before these columns were added to
-- migrations/001_init.sql; a fresh install already gets them from 001_init.sql.
ALTER TABLE seasons ADD COLUMN is_demo TINYINT(1) NOT NULL DEFAULT 0 AFTER is_current;
ALTER TABLE teams ADD COLUMN is_demo TINYINT(1) NOT NULL DEFAULT 0 AFTER pin;
