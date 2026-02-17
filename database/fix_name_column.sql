-- First, check if the 'name' column exists:
-- Run this to see the table structure:
DESCRIBE budget_requests;

-- If 'name' column is NOT in the list, run this to add it:
ALTER TABLE budget_requests
  ADD COLUMN name VARCHAR(255) NULL AFTER request_id;

-- If you get "Duplicate column name 'name'", the column already exists.
-- In that case, check your database connection or table name - you might be connected to a different database.
