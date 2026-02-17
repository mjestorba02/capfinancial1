-- Run this in phpMyAdmin (or your MySQL client) to add workflow and details to budget_requests.
-- If you get "Duplicate column name", that column already exists; skip that line or comment it out.

-- 1. Add details column (after remarks)
ALTER TABLE budget_requests
  ADD COLUMN details TEXT NULL AFTER remarks;

-- 2. Add attachment_path column (after image_path)
-- If you get "Unknown column 'image_path'", use: ADD COLUMN attachment_path VARCHAR(255) NULL AFTER details;
ALTER TABLE budget_requests
  ADD COLUMN attachment_path VARCHAR(255) NULL AFTER image_path;

-- 3. Add hr_approved_at column
ALTER TABLE budget_requests
  ADD COLUMN hr_approved_at TIMESTAMP NULL AFTER attachment_path;

-- 4. Add admin_approved_at column
ALTER TABLE budget_requests
  ADD COLUMN admin_approved_at TIMESTAMP NULL AFTER hr_approved_at;

-- 5. Extend status enum to include 'Pending Admin'
ALTER TABLE budget_requests
  MODIFY COLUMN status ENUM('Pending', 'Pending Admin', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending';

-- Optional: Mark this migration as run so Laravel won't run it again.
-- Uncomment and run after the above:
-- SET @batch = (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations);
-- INSERT INTO migrations (migration, batch) VALUES ('2026_02_17_000000_add_workflow_and_details_to_budget_requests', @batch);
