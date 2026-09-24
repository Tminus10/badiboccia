-- BadiBoccia schema (MySQL / MariaDB, utf8mb4)

CREATE TABLE seasons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(100) NOT NULL,
  year INT NOT NULL,
  is_current TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE team_groups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  season_id INT NOT NULL,
  name CHAR(1) NOT NULL,
  FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_season_group (season_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- A team is a persistent pairing (e.g. "Debi & Erika"): it keeps the same id,
-- PIN, photo and color across every season it plays. Which group/season it's
-- enrolled in for a given year lives in team_seasons below.
CREATE TABLE teams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  player1 VARCHAR(100) NOT NULL,
  player2 VARCHAR(100) NOT NULL,
  color_hex CHAR(7) NOT NULL,
  photo_path VARCHAR(255) NULL,
  pin VARCHAR(4) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- One row per team per season it's enrolled in: which group it plays in.
CREATE TABLE team_seasons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team_id INT NOT NULL,
  season_id INT NOT NULL,
  group_id INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE,
  FOREIGN KEY (group_id) REFERENCES team_groups(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_team_season (team_id, season_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(100) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE games (
  id INT AUTO_INCREMENT PRIMARY KEY,
  season_id INT NOT NULL,
  phase VARCHAR(10) NOT NULL DEFAULT 'group', -- group | qf | sf | final
  group_id INT NULL,
  slot_index INT NULL,
  team_a_id INT NULL,
  team_b_id INT NULL,
  sets_a TINYINT NULL,
  sets_b TINYINT NULL,
  played_date DATE NULL,
  game_time TIME NULL, -- optional kickoff time for played_date; NULL means "all day" in the calendar
  next_game_id INT NULL,
  next_game_slot CHAR(1) NULL, -- a | b
  updated_by_type VARCHAR(10) NULL, -- team | admin
  updated_by_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE,
  FOREIGN KEY (group_id) REFERENCES team_groups(id) ON DELETE CASCADE,
  FOREIGN KEY (team_a_id) REFERENCES teams(id) ON DELETE SET NULL,
  FOREIGN KEY (team_b_id) REFERENCES teams(id) ON DELETE SET NULL,
  FOREIGN KEY (next_game_id) REFERENCES games(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  game_id INT NOT NULL,
  actor_type VARCHAR(10) NOT NULL,
  actor_id INT NOT NULL,
  actor_label VARCHAR(150) NULL,
  old_sets_a TINYINT NULL,
  old_sets_b TINYINT NULL,
  old_played_date DATE NULL,
  new_sets_a TINYINT NULL,
  new_sets_b TINYINT NULL,
  new_played_date DATE NULL,
  changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin-created calendar entries with no game behind them (group draw, victory party, etc.).
-- Shown alongside games in the calendar views and the .ics feed.
CREATE TABLE calendar_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  season_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  location VARCHAR(255) NULL,
  event_date DATE NOT NULL,
  event_time TIME NULL, -- NULL means "all day" in the calendar, same as games.game_time
  duration_minutes SMALLINT NULL, -- NULL falls back to the default custom-event duration in the .ics feed
  reminder TINYINT(1) NOT NULL DEFAULT 1, -- 1h-before VALARM in the .ics feed; only applies when event_time is set
  created_by_admin_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by_admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE team_login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team_id INT NOT NULL,
  success TINYINT(1) NOT NULL,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
