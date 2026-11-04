-- Knockout games where the winner is known but not the score: stored as 2:0 for the winner (the
-- bracket and podium need a result to work with) and flagged, so the site and the app show
-- "winner known, result unknown" instead of a made-up score. Entering a real result clears it.
-- Run once against an existing database that was set up before these columns were added to
-- migrations/001_init.sql; a fresh install already gets them from 001_init.sql.
ALTER TABLE games ADD COLUMN result_estimated TINYINT(1) NOT NULL DEFAULT 0 AFTER sets_b;
ALTER TABLE audit_log ADD COLUMN old_result_estimated TINYINT(1) NOT NULL DEFAULT 0 AFTER old_sets_b;
ALTER TABLE audit_log ADD COLUMN new_result_estimated TINYINT(1) NOT NULL DEFAULT 0 AFTER new_sets_b;
