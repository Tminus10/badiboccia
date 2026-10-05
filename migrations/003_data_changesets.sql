-- Records which data changesets (scripted, reviewed bulk edits such as the WhatsApp history
-- import in data-changes/) have been applied to this database, so DEV and PROD each know
-- which ones they already have and scripts/apply-changeset.php never applies one twice.
-- Run once against an existing database that was set up before this table was added to
-- migrations/001_init.sql; a fresh install already gets it from 001_init.sql.
CREATE TABLE data_changesets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  summary TEXT NULL,
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_changeset_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
