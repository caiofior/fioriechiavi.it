ALTER TABLE `taxa` ADD COLUMN `eol_id` BIGINT(20) UNSIGNED DEFAULT NULL COMMENT 'Id of taxa in Enciclopedia of life' AFTER `col_id`;