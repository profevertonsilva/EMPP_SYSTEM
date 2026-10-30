/* Email verification for researcher sign-up: a 6-digit code is emailed and
   must be typed back before the first sign-in. The code's hash lives in
   log_token. Run once on an existing database. */

ALTER TABLE login
    ADD COLUMN log_code_expires datetime NULL AFTER log_token,
    ADD COLUMN log_code_attempts tinyint NOT NULL DEFAULT 0 AFTER log_code_expires;

/* Accounts created before this feature are treated as confirmed */
UPDATE login SET log_confirmed = 'Y';

/* Fill in what the stricter columns below no longer accept */
UPDATE login SET log_token = '' WHERE log_token IS NULL;
UPDATE login SET log_status = 'A' WHERE log_status IS NULL OR log_status = '';
UPDATE login SET log_create = NOW() WHERE log_create IS NULL;

/* log_token grows to 255 so no existing value is cut (the code hash needs 60) */
ALTER TABLE login
    MODIFY log_status enum('A','I') NOT NULL DEFAULT 'A',
    MODIFY log_confirmed enum('Y','N') NOT NULL DEFAULT 'N',
    MODIFY log_token varchar(255) NOT NULL DEFAULT '',
    MODIFY log_create timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;
