# Database Structure

This schema supports:
- Register using email and password
- Login using email and password
- Reset password via emailed link
- Login via one-time passcode (OTP) sent to email

Target database: MySQL/MariaDB (XAMPP), `InnoDB`, `utf8mb4`.

Prefix: All table names start with `services_`.

## Tables

### services_users
- Stores user accounts and credential state.

```sql
CREATE TABLE IF NOT EXISTS `services_users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NULL,
  `middle_name` VARCHAR(100) NULL,
  `last_name` VARCHAR(100) NULL,
  `suffix` VARCHAR(20) NULL, -- Jr., Sr., III, etc.
  `password_hash` VARCHAR(255) NULL, -- bcrypt/argon2 encoded string
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0, -- 0 = not verified, 1 = verified
  `email_verified_at` DATETIME NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

Notes:
- Store email in a case-insensitive collation to avoid duplicates by case.
- `password_hash` can be `NULL` for users who use OTP-only (passwordless).
- Name fields (`first_name`, `last_name`, `middle_name`, `suffix`) are collected after registration and are all nullable.
- `first_name` and `last_name` will be requested from users after they complete registration.
- `middle_name` and `suffix` are optional fields for additional name information.
- `email_verified` defaults to 0 (unverified) and is set to 1 after successful OTP verification.
- `email_verified_at` is set when the user successfully verifies their email via OTP.

### services_password_resets
- Stores password reset links (token hashes) and their lifecycle.

```sql
CREATE TABLE IF NOT EXISTS `services_password_resets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `token_hash` CHAR(64) NOT NULL, -- SHA-256 hex of the reset token
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `requested_ip` VARCHAR(45) NULL,
  `used_ip` VARCHAR(45) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_password_resets_token` (`token_hash`),
  KEY `idx_password_resets_user` (`user_id`),
  CONSTRAINT `fk_services_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `services_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

Notes:
- Always store a hash of the token; never store the raw token.
- Mark `used_at` when consumed and reject if expired or already used.

### services_email_otp_codes
- Stores one-time codes for email-based OTP login.

```sql
CREATE TABLE IF NOT EXISTS `services_email_otp_codes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `six_digit` VARCHAR(50) NOT NULL,
  `purpose` ENUM('login', 'register') NOT NULL DEFAULT 'login',
  `sent_to` VARCHAR(255) NOT NULL, -- email used to send the code
  `expires_at` DATETIME NOT NULL,
  `consumed_at` DATETIME NULL,
  `attempts` INT NOT NULL DEFAULT 0, -- number of validation attempts
  `max_attempts` INT NOT NULL DEFAULT 5,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email_otp_code` (`six_digit`),
  KEY `idx_email_otp_user` (`user_id`),
  CONSTRAINT `fk_services_email_otp_user` FOREIGN KEY (`user_id`) REFERENCES `services_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

Notes:
- `six_digit` stores the actual 6-digit code. Enforce digits-only on the client and server.
- The unique key on `six_digit` avoids duplicate active codes across the table. Be mindful of collision risk at scale; purge expired rows promptly.
- `purpose` is limited to `login` or `register` to distinguish flows.
- `sent_to` captures the email address used when sending the OTP (may match the user’s primary email).
- Set `expires_at` based on `expiration_config` (e.g., `login_otp` for login, `activation_account` for register).
- Increment `attempts` on every verification try; block once `attempts >= max_attempts` and optionally require resend.
- Mark success by setting `consumed_at`; do not allow reuse after consumption or expiry.

### expiration_config
- Centralized TTL configuration for OTPs, resets, and sessions.

```sql
CREATE TABLE IF NOT EXISTS `expiration_config` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(50) NOT NULL,
  `interval_value` INT(11) NOT NULL,
  `interval_unit` ENUM('MINUTE','HOUR','DAY','MONTH') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_expiration_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

Recommended seed data:

```sql
INSERT INTO `expiration_config` (`type`, `interval_value`, `interval_unit`) VALUES
('activation_account', 1, 'DAY'),
('password_reset', 1, 'DAY'),
('login_otp', 10, 'MINUTE'),
('session', 7, 'DAY');
```

## Recommended Indexes
- `services_users.email` unique for fast lookup on login and registration.
- Foreign keys from `services_password_resets` and `services_email_otp_codes` to `services_users` with `ON DELETE CASCADE` to clean up automatically.
- `expiration_config.type` unique to ensure one config per type.

## Workflows

### Register (email + password)
- Validate email format and uniqueness in `services_users`.
- Hash password with `bcrypt` or `argon2` and store in `services_users.password_hash`.
- Insert new row in `services_users` with `is_active = 1` and `email_verified = 0`.

### Email Verification (after registration)
- Generate a 6-digit numeric OTP and insert into `services_email_otp_codes.six_digit`.
- Set `purpose = 'register'`, derive `expires_at` using `expiration_config` where `type = 'activation_account'`, and email the code to the user (`sent_to`).
- When the user submits the OTP:
  - Compare input against `six_digit` for the correct `user_id`, ensure not expired, not consumed, and `attempts < max_attempts`.
  - Increment `attempts` per try; on success set `consumed_at` in the OTP table.
  - Update `services_users` set `email_verified = 1` and `email_verified_at = NOW()`.

### Login (email + password)
- Lookup `services_users` by `email` and ensure `is_active = 1`.
- Verify submitted password against `services_users.password_hash`.
- On success, update `services_users.last_login_at` and issue a session/cookie (app-layer).

### Reset Password via Link
- Generate a random 32–64 byte token; store `SHA-256` hex as `services_password_resets.token_hash`.
- Set `expires_at` (e.g., 30–60 minutes) and email the link containing the raw token.
- When the link is opened:
  - Lookup `services_password_resets` by `token_hash` (hash the raw token first).
  - Ensure not expired and `used_at IS NULL`.
  - Accept new password; update `services_users.password_hash`.
  - Set `used_at` and optionally delete other active reset tokens for the user.

### Login via Email OTP
- Generate a 6-digit numeric code and store it in `services_email_otp_codes.six_digit` with TTL (e.g., 5–10 minutes).
- Email the code to `sent_to` with clear instructions and expiry.
- When the user submits the code:
  - Match by `six_digit` and `user_id`, ensure `expires_at` not passed, `consumed_at IS NULL`, and `attempts < max_attempts`.
  - Increment `attempts` per try; on success set `consumed_at` and authenticate the user.

## Security Guidelines
- Always hash secrets: password (`bcrypt`/`argon2`), long-lived tokens (`SHA-256` hex).
- Use secure random source for tokens/codes (e.g., `random_bytes` in PHP).
- Rate-limit OTP and password reset requests per email/IP.
- Prefer HTTPS in production; ensure tokens are single-use and short-lived.
- Consider soft fails on enumeration: do not disclose whether an email exists.

## Integration Notes (PHP)
- Use `password_hash()` / `password_verify()` for password handling.
- Use `random_int(100000, 999999)` or equivalent to generate 6-digit OTPs.
- If storing raw `six_digit`, ensure strict rate limiting and short TTL; optionally switch to storing a hash in future if requirements change.
- For case-insensitive email matching, rely on `utf8mb4_general_ci` and always `trim()` inputs.