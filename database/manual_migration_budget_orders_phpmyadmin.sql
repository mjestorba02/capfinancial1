-- ============================================================
-- Manual migration: Budget module (Budget Orders + Collections Ordered)
-- Run this in phpMyAdmin (SQL tab). Run each section one at a time.
-- If you get "Duplicate column" or "Column already exists", skip that line.
-- ============================================================

-- ------------------------------------------------------------
-- 1. Add budget_request_id to collections (if not already there)
-- ------------------------------------------------------------
ALTER TABLE `collections`
ADD COLUMN `budget_request_id` BIGINT UNSIGNED NULL AFTER `id`;

-- If you get "Duplicate column name 'budget_request_id'", that column already exists. Skip to step 2.


-- ------------------------------------------------------------
-- 2. Add employee_id to collections (if not already there)
-- ------------------------------------------------------------
ALTER TABLE `collections`
ADD COLUMN `employee_id` BIGINT UNSIGNED NULL AFTER `budget_request_id`;

-- If you get "Duplicate column name 'employee_id'", that column already exists. Skip to step 3.


-- ------------------------------------------------------------
-- 3. Add 'Ordered' to the status enum on collections
-- ------------------------------------------------------------
ALTER TABLE `collections`
MODIFY COLUMN `status` ENUM('Pending','Paid','Overdue','Ordered') NOT NULL DEFAULT 'Pending';


-- ------------------------------------------------------------
-- 4. Create budget_orders table
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `budget_orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `budget_request_id` BIGINT UNSIGNED NOT NULL,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `material_description` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `receipt_number` VARCHAR(255) NOT NULL,
  `receipt_path` VARCHAR(255) NULL DEFAULT NULL,
  `collection_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `remarks` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `budget_orders_receipt_number_unique` (`receipt_number`),
  KEY `budget_orders_budget_request_id_foreign` (`budget_request_id`),
  KEY `budget_orders_employee_id_foreign` (`employee_id`),
  KEY `budget_orders_collection_id_foreign` (`collection_id`),
  CONSTRAINT `budget_orders_budget_request_id_foreign` FOREIGN KEY (`budget_request_id`) REFERENCES `budget_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `budget_orders_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `budget_orders_collection_id_foreign` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Done. You can now use the Budget module in the employee portal.
-- ============================================================
