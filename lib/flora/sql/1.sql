SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema flora
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `flora` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `flora` ;

-- -----------------------------------------------------
-- Table `flora`.`taxa_kind`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `flora`.`taxa_kind` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Taxa kind';


-- -----------------------------------------------------
-- Table `flora`.`taxa`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `flora`.`taxa` (
  `id` INT NOT NULL COMMENT 'Id of taxonomy',
  `taxonomy_kind` INT NULL COMMENT 'Taxonomy kind',
  `name` VARCHAR(100) NULL COMMENT 'Taxonomy name',
  `description` TEXT NULL COMMENT 'Taxonomi description',
  `dico_id` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_taxonomy_kind_idx` (`taxonomy_kind` ASC),
  CONSTRAINT `fk_taxonomy_kind`
    FOREIGN KEY (`taxonomy_kind`)
    REFERENCES `flora`.`taxa_kind` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Taxa';


-- -----------------------------------------------------
-- Table `flora`.`taxa_image`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `flora`.`taxa_image` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Id of taxa image',
  `id_taxa` INT NULL COMMENT 'Id of taxa',
  `filename` VARCHAR(200) NULL COMMENT 'Filename',
  PRIMARY KEY (`id`),
  INDEX `fk_taxa_image_1_idx` (`id_taxa` ASC),
  CONSTRAINT `fk_taxa_image_1`
    FOREIGN KEY (`id_taxa`)
    REFERENCES `flora`.`taxa_image` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Taxa images';


-- -----------------------------------------------------
-- Table `flora`.`taxa_attribute`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `flora`.`taxa_attribute` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Id of taxa attribute',
  `name` VARCHAR(100) NULL COMMENT 'Taxa attribute name',
  `description` TEXT NULL COMMENT 'Taxa attribute desciption',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Taxa attribute';


-- -----------------------------------------------------
-- Table `flora`.`taxa_attribute_value`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `flora`.`taxa_attribute_value` (
  `id_taxa` INT NOT NULL COMMENT 'Id of taxa',
  `id_taxa_attribute` INT NOT NULL COMMENT 'Id of taxa attribute',
  `value` VARCHAR(100) NULL COMMENT 'Value',
  PRIMARY KEY (`id_taxa`, `id_taxa_attribute`),
  CONSTRAINT `fk_taxa_attribute_value_taxa`
    FOREIGN KEY (`id_taxa`)
    REFERENCES `flora`.`taxa` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_taxa_attribute_value_taxa_attribute`
    FOREIGN KEY (`id_taxa_attribute`)
    REFERENCES `flora`.`taxa_attribute` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Taxa attribute value';


-- -----------------------------------------------------
-- Table `flora`.`dico`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `flora`.`dico` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Dicotomy';


-- -----------------------------------------------------
-- Table `flora`.`dico_item`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `flora`.`dico_item` (
  `id` VARCHAR(100) NOT NULL,
  `id_dico` INT NOT NULL,
  `text` VARCHAR(100) NULL,
  `taxa_id` INT NULL,
  PRIMARY KEY (`id`, `id_dico`),
  INDEX `fk_dico_item_idx` (`id_dico` ASC),
  INDEX `fk_dico_item_taxa_idx` (`taxa_id` ASC),
  CONSTRAINT `fk_dico_item`
    FOREIGN KEY (`id_dico`)
    REFERENCES `flora`.`dico` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_dico_item_taxa`
    FOREIGN KEY (`taxa_id`)
    REFERENCES `flora`.`taxa` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Dicotomy item';


-- -----------------------------------------------------
-- Table `flora`.`region`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `flora`.`region` (
  `id` VARCHAR(20) NOT NULL COMMENT 'Region id',
  `description` VARCHAR(100) NULL COMMENT 'Region description',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Region description';


-- -----------------------------------------------------
-- Table `flora`.`taxa_region`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `flora`.`taxa_region` (
  `id_taxa` INT NOT NULL,
  `id_region` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id_taxa`, `id_region`),
  INDEX `fk_taxa_region_region_idx` (`id_region` ASC),
  CONSTRAINT `fk_taxa_region_taxa`
    FOREIGN KEY (`id_taxa`)
    REFERENCES `flora`.`taxa` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_taxa_region_region`
    FOREIGN KEY (`id_region`)
    REFERENCES `flora`.`region` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Association between taxa and region';


-- -----------------------------------------------------
-- Table `flora`.`user_role`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `flora`.`user_role` (
  `id` INT NOT NULL,
  `description` VARCHAR(50) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'User role';


-- -----------------------------------------------------
-- Table `flora`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `flora`.`user` (
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(100) NULL,
  `active` SMALLINT NULL,
  `profile_id` INT NULL,
  `role_id` INT NULL,
  `creation_datetime` DATETIME NULL COMMENT 'user creation datetime',
  `change_datetime` DATETIME NULL COMMENT 'user last modify date time',
  `confirm_datetime` DATETIME NULL COMMENT 'confirm datet time',
  `last_login_datetime` DATETIME NULL,
  `confirm_code` VARCHAR(50) NULL COMMENT 'confirm code',
  PRIMARY KEY (`username`),
  INDEX `fk_user_role_idx` (`role_id` ASC),
  CONSTRAINT `fk_user_role`
    FOREIGN KEY (`role_id`)
    REFERENCES `flora`.`user_role` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'User data';


-- -----------------------------------------------------
-- Table `flora`.`profile`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `flora`.`profile` (
  `id` INT NOT NULL,
  `first_name` VARCHAR(100) NULL,
  `last_name` VARCHAR(100) NULL,
  `address` VARCHAR(200) NULL,
  `city` VARCHAR(100) NULL,
  `province` VARCHAR(100) NULL,
  `state` VARCHAR(100) NULL,
  `phone` VARCHAR(50) NULL,
  `email` VARCHAR(100) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Profile';


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
