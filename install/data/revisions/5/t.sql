ALTER TABLE `profile` ADD `token` VARCHAR(36) NOT NULL COMMENT 'token' AFTER `expire`;
UPDATE `profile` SET `token`=UUID();
ALTER TABLE `fioriech65618`.`profile` ADD UNIQUE `token` (`token`);