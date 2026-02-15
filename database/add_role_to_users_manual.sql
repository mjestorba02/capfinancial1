-- Run this in phpMyAdmin (or your host's SQL runner) when you have migration issues.
-- Adds the `role` column to users table for admin/hr account types.
-- If you get "Duplicate column name 'role'", the column already exists; skip the ALTER.

-- Add role column (VARCHAR(20), default 'admin', after email)
ALTER TABLE users
  ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'admin' AFTER email;

-- Ensure all existing users have role = 'admin' (in case of any edge cases)
UPDATE users SET role = 'admin' WHERE role IS NULL OR role = '';

-- Optional: Mark this migration as already run so Laravel won't run it again.
-- Uncomment and run these two lines after the ALTER above:
-- SET @batch = (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations);
-- INSERT INTO migrations (migration, batch) VALUES ('2026_02_15_000000_add_role_to_users_table', @batch);
