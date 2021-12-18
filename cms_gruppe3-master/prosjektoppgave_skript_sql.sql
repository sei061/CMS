-- MySQL Script generated by MySQL Workbench
-- Tue May 12 23:10:05 2020
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema stud_v20_aaoyen
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Table `CMS_Catalog`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `CMS_Catalog` ;

CREATE TABLE IF NOT EXISTS `CMS_Catalog` (
  `PK_Catalog` INT(11) NOT NULL AUTO_INCREMENT,
  `catalogname` VARCHAR(45) NOT NULL,
  `FK_ParentKey` INT(11) NOT NULL,
  PRIMARY KEY (`PK_Catalog`, `FK_ParentKey`),
  INDEX `fk_CMS_Catalog_CMS_Catalog_idx` (`FK_ParentKey` ASC),
  CONSTRAINT `fk_CMS_Catalog_CMS_Catalog`
    FOREIGN KEY (`FK_ParentKey`)
    REFERENCES `CMS_Catalog` (`PK_Catalog`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 41
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `CMS_User`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `CMS_User` ;

CREATE TABLE IF NOT EXISTS `CMS_User` (
  `PK_User` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(45) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(45) NOT NULL,
  `datecreated` DATE NOT NULL,
  `verification` VARCHAR(32) NOT NULL,
  `activated` INT(1) NOT NULL,
  `user_type` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`PK_User`),
  UNIQUE INDEX `username` (`username` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 24
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `CMS_Document`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `CMS_Document` ;

CREATE TABLE IF NOT EXISTS `CMS_Document` (
  `PK_Document` INT(11) NOT NULL AUTO_INCREMENT,
  `filesize` INT(15) NOT NULL,
  `filename` VARCHAR(45) NOT NULL,
  `filetype` VARCHAR(45) NOT NULL,
  `filecode` MEDIUMBLOB NOT NULL,
  `dateuploaded` DATE NOT NULL,
  `dateedited` DATE NOT NULL,
  `description` MEDIUMTEXT NULL DEFAULT NULL,
  `public` INT(1) NOT NULL DEFAULT 0,
  `FK_Catalog` INT(11) NOT NULL,
  `FK_User` INT(11) NOT NULL,
  PRIMARY KEY (`PK_Document`, `FK_Catalog`, `FK_User`),
  INDEX `fk_CMS_Document_CMS_Catalog1_idx` (`FK_Catalog` ASC),
  INDEX `fk_CMS_Document_CMS_User1_idx` (`FK_User` ASC),
  CONSTRAINT `fk_CMS_Document_CMS_Catalog1`
    FOREIGN KEY (`FK_Catalog`)
    REFERENCES `CMS_Catalog` (`PK_Catalog`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_CMS_Document_CMS_User1`
    FOREIGN KEY (`FK_User`)
    REFERENCES `CMS_User` (`PK_User`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 60
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `CMS_Comment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `CMS_Comment` ;

CREATE TABLE IF NOT EXISTS `CMS_Comment` (
  `PK_Comment` INT(11) NOT NULL AUTO_INCREMENT,
  `text` MEDIUMTEXT CHARACTER SET 'utf8mb4' NOT NULL,
  `datecreated` DATE NOT NULL,
  `FK_Document` INT(11) NOT NULL,
  `FK_User` INT(11) NOT NULL,
  PRIMARY KEY (`PK_Comment`, `FK_Document`, `FK_User`),
  INDEX `fk_CMS_Comment_CMS_Document1_idx` (`FK_Document` ASC),
  INDEX `fk_CMS_Comment_CMS_User1_idx` (`FK_User` ASC),
  CONSTRAINT `fk_CMS_Comment_CMS_Document1`
    FOREIGN KEY (`FK_Document`)
    REFERENCES `CMS_Document` (`PK_Document`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_CMS_Comment_CMS_User1`
    FOREIGN KEY (`FK_User`)
    REFERENCES `CMS_User` (`PK_User`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 67
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `CMS_Logs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `CMS_Logs` ;

CREATE TABLE IF NOT EXISTS `CMS_Logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `User` INT(11) NOT NULL,
  `Document` INT(11) NOT NULL,
  `text` MEDIUMTEXT CHARACTER SET 'utf8mb4' NOT NULL,
  `Comment` INT(11) NOT NULL,
  `action` VARCHAR(20) NOT NULL,
  `cdate` DATETIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `CMS_Tag`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `CMS_Tag` ;

CREATE TABLE IF NOT EXISTS `CMS_Tag` (
  `PK_Tag` INT(11) NOT NULL AUTO_INCREMENT,
  `tagtext` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`PK_Tag`),
  UNIQUE INDEX `tagtext` (`tagtext` ASC),
  UNIQUE INDEX `PK_Tag` (`PK_Tag` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 31
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `Tag_has_Document`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Tag_has_Document` ;

CREATE TABLE IF NOT EXISTS `Tag_has_Document` (
  `FK_Tag` INT(11) NOT NULL,
  `FK_Document` INT(11) NOT NULL,
  PRIMARY KEY (`FK_Tag`, `FK_Document`),
  INDEX `fk_CMS_Tag_has_CMS_Document_CMS_Document1_idx` (`FK_Document` ASC),
  INDEX `fk_CMS_Tag_has_CMS_Document_CMS_Tag1_idx` (`FK_Tag` ASC),
  CONSTRAINT `fk_CMS_Tag_has_CMS_Document_CMS_Document1`
    FOREIGN KEY (`FK_Document`)
    REFERENCES `CMS_Document` (`PK_Document`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_CMS_Tag_has_CMS_Document_CMS_Tag1`
    FOREIGN KEY (`FK_Tag`)
    REFERENCES `CMS_Tag` (`PK_Tag`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


DELIMITER $$

DROP TRIGGER IF EXISTS `DeleteLog` $$
CREATE
DEFINER=`stud_v20_aaoyen`@`%`
TRIGGER `stud_v20_aaoyen`.`DeleteLog`
AFTER DELETE ON `stud_v20_aaoyen`.`CMS_Comment`
FOR EACH ROW
INSERT INTO CMS_Logs VALUES(null, old.FK_User, old.FK_Document, old.text, old.PK_Comment, "Deleted", NOW())$$


DELIMITER ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;