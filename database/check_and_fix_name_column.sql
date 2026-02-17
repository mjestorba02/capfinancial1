-- Step 1: Check current database
SELECT DATABASE();

-- Step 2: List all tables to verify table name
SHOW TABLES LIKE '%budget%';

-- Step 3: Check current structure of budget_requests
DESCRIBE budget_requests;

-- Step 4: Try adding the name column (if it doesn't exist)
-- If you get "Duplicate column name", skip this
ALTER TABLE budget_requests
  ADD COLUMN IF NOT EXISTS name VARCHAR(255) NULL AFTER request_id;

-- Note: MySQL 5.7+ doesn't support IF NOT EXISTS for ALTER TABLE ADD COLUMN
-- So if the above fails, use this instead (check first with DESCRIBE):
ALTER TABLE budget_requests
  ADD COLUMN name VARCHAR(255) NULL AFTER request_id;

-- Step 5: Verify it was added
DESCRIBE budget_requests;
