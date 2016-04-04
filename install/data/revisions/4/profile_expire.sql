ALTER TABLE `profile` ADD `expire` DATE NOT NULL AFTER `email`;
ALTER TABLE `dico_item` ADD `photo_id` INT NOT NULL COMMENT 'Photo id' AFTER `taxa_id`;
ALTER TABLE `add_dico_item` ADD `photo_id` INT NOT NULL COMMENT 'Photo id' AFTER `taxa_id`;
ALTER TABLE `taxa` ADD `is_list` SMALLINT(1) NOT NULL COMMENT 'Is list' AFTER `eol_id`;
ALTER TABLE `add_dico` ADD `is_list` SMALLINT(1) NOT NULL COMMENT 'Is list' AFTER `name`;
