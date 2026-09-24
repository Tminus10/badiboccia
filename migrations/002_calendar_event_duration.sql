-- Adds an optional duration to admin-created calendar entries (group draw, victory party,
-- etc.), so the .ics feed no longer has to assume a fixed 1-hour slot for every custom event.
-- Run once against an existing database that was set up before this column was added to
-- migrations/001_init.sql; a fresh install already gets it from 001_init.sql.
ALTER TABLE calendar_events
  ADD COLUMN duration_minutes SMALLINT NULL AFTER event_time;
