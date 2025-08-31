-- Add stamp and admin columns to tbl_reciept table
ALTER TABLE `tbl_reciept` ADD COLUMN `stamp` DECIMAL(10,2) NULL AFTER `center_id`;
ALTER TABLE `tbl_reciept` ADD COLUMN `admin` DECIMAL(10,2) NULL AFTER `stamp`;

-- Add indexes for better performance
ALTER TABLE `tbl_reciept` ADD INDEX `idx_stamp` (`stamp`);
ALTER TABLE `tbl_reciept` ADD INDEX `idx_admin` (`admin`); 