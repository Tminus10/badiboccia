-- Bearer tokens for the JSON API (/api/v1), used by the iOS/iPadOS app: a team logs in with
-- its PIN once and the app keeps the token instead of a session cookie. Only a SHA-256 hash
-- of each token is stored. Resetting a team's PIN revokes all of its tokens.
-- Run once against an existing database that was set up before this table was added to
-- migrations/001_init.sql; a fresh install already gets it from 001_init.sql.
CREATE TABLE api_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  token_hash CHAR(64) NOT NULL,
  team_id INT NOT NULL,
  device_label VARCHAR(100) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_used_at DATETIME NULL,
  UNIQUE KEY uniq_token_hash (token_hash),
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
