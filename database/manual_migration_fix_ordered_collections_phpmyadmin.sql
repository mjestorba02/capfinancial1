-- ============================================================
-- Manual migration: Fix Ordered (Budget Order) Collections Display
-- Run this in phpMyAdmin (SQL tab).
-- Select your database first, then paste and execute.
-- ============================================================
-- This will:
--   1. Remove " (Budget Order)" from customer_name
--   2. Set amount_paid = amount_due
--   3. Set payment_date = transaction date (created_at)
-- ============================================================

UPDATE `collections`
SET
  `customer_name` = TRIM(REPLACE(COALESCE(`customer_name`, ''), ' (Budget Order)', '')),
  `amount_paid` = `amount_due`,
  `payment_date` = DATE(`created_at`)
WHERE `status` = 'Ordered'
  AND (`employee_id` IS NOT NULL OR `budget_request_id` IS NOT NULL);

-- ============================================================
-- Done. Refresh the Collections page to see the updates.
-- ============================================================
