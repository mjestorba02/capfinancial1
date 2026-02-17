-- Make sure you're using the correct database: fina_finance1
-- Run this in phpMyAdmin or MySQL client:

USE fina_finance1;

-- Check if name column exists
DESCRIBE budget_requests;

-- If name column is NOT in the list above, run this:
ALTER TABLE fina_finance1.budget_requests
  ADD COLUMN name VARCHAR(255) NULL AFTER request_id;

-- Or if you're already in the fina_finance1 database:
ALTER TABLE budget_requests
  ADD COLUMN name VARCHAR(255) NULL AFTER request_id;

-- Verify it was added
DESCRIBE budget_requests;

-- You should now see 'name' in the column list
