-- Add center_id column to tbl_reciept table
ALTER TABLE `tbl_reciept` ADD COLUMN `center_id` INT(11) NULL AFTER `receipt_num`;

-- Add foreign key constraint (optional - for referential integrity)
-- ALTER TABLE `tbl_reciept` ADD CONSTRAINT `fk_receipt_center` FOREIGN KEY (`center_id`) REFERENCES `centers`(`id`) ON DELETE SET NULL;

-- Add index for better performance
ALTER TABLE `tbl_reciept` ADD INDEX `idx_center_id` (`center_id`); 