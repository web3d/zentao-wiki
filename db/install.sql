CREATE  TABLE IF NOT EXIST `zt_wiki_pages` (
  `id` INT NOT NULL ,
  `product_id` INT NULL ,
  `revision` INT NULL ,
  `published` TINYINT NULL DEFAULT 0 ,
  `is_index` TINYINT NULL DEFAULT 0
  `locked` TINYINT NULL DEFAULT 0 ,
  `locked_by_id` VARCHAR(45) NULL ,
  `locked_on` INT NULL ,
  `company` MEDIUMINT(9) NOT NULL,
  PRIMARY KEY (`id`) ,
  INDEX `product_id`);
  
CREATE  TABLE IF NOT EXIST `zt_wiki_revisions` (
  `id` INT NOT NULL ,
  `company` MEDIUMINT NULL ,
  `page_id` INT NOT NULL ,
  `created_on` INT NOT NULL ,
  `created_by_id` INT NOT NULL ,
  `name` VARCHAR(100) NULL ,
  `product_id` INT NOT NULL ,
  `revision` INT NOT NULL ,
  `content` TEXT NULL ,
  `log_message` VARCHAR(50) NULL ,
  PRIMARY KEY (`id`) ,
INDEX `page_id` ,
INDEX `revision`,
  );



