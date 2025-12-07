-- ========================================
-- QUICK REFERENCE: Database Update Commands
-- ========================================
-- File: UPDATE_ACTIVITY_FIELDS.sql
-- Purpose: Change activity fields from TINYINT to SMALLINT
-- Date: December 4, 2025
-- ========================================

-- STEP 1: Backup current data (IMPORTANT!)
-- ========================================
CREATE TABLE workplan_budget_items_backup AS 
SELECT * FROM workplan_budget_items;

-- STEP 2: Modify column types
-- ========================================
ALTER TABLE `workplan_budget_items` 
MODIFY COLUMN `activity_jan` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `activity_feb` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `activity_mar` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `activity_apr` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `activity_may` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `activity_jun` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `activity_jul` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `activity_aug` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `activity_sep` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `activity_oct` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `activity_nov` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `activity_dec` SMALLINT UNSIGNED NOT NULL DEFAULT 0;

-- STEP 3: Verify the changes
-- ========================================
DESCRIBE `workplan_budget_items`;

-- Expected output for activity fields:
-- Field         | Type                  | Null | Key | Default | Extra
-- activity_jan  | smallint(5) unsigned  | NO   |     | 0       |
-- activity_feb  | smallint(5) unsigned  | NO   |     | 0       |
-- ... (etc)

-- STEP 4: Check data integrity
-- ========================================
SELECT 
    COUNT(*) as total_records,
    MAX(activity_jan) as max_jan,
    MAX(activity_feb) as max_feb,
    MAX(activity_mar) as max_mar,
    MAX(activity_apr) as max_apr,
    MAX(activity_may) as max_may,
    MAX(activity_jun) as max_jun,
    MAX(activity_jul) as max_jul,
    MAX(activity_aug) as max_aug,
    MAX(activity_sep) as max_sep,
    MAX(activity_oct) as max_oct,
    MAX(activity_nov) as max_nov,
    MAX(activity_dec) as max_dec
FROM workplan_budget_items;

-- STEP 5: Drop backup table (optional, after verification)
-- ========================================
-- DROP TABLE workplan_budget_items_backup;

-- ========================================
-- ROLLBACK PROCEDURE (if needed)
-- ========================================
-- If something goes wrong, restore from backup:
-- 
-- TRUNCATE TABLE workplan_budget_items;
-- INSERT INTO workplan_budget_items SELECT * FROM workplan_budget_items_backup;
-- ========================================
