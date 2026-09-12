-- Switches team PINs from a one-way bcrypt hash to plaintext storage so
-- admins can look a team's PIN up again later (requested feature). This is an
-- acceptable trade-off here: a 4-digit code protecting nothing more sensitive
-- than a casual beach-league game score, already behind rate-limited login
-- attempts and HTTPS in transit.
--
-- Existing PINs (already bcrypt-hashed) cannot be recovered, so this
-- regenerates a fresh random PIN for every existing team.

ALTER TABLE teams ADD COLUMN pin VARCHAR(4) NULL AFTER pin_hash;
UPDATE teams SET pin = LPAD(FLOOR(RAND() * 10000), 4, '0');
ALTER TABLE teams MODIFY COLUMN pin VARCHAR(4) NOT NULL;
ALTER TABLE teams DROP COLUMN pin_hash;
