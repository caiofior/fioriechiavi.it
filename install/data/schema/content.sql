CREATE TABLE `content` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id',
  `label` varchar(45) NOT NULL,
  `category_id` int(11) DEFAULT NULL COMMENT 'Category id',
  `title` varchar(200) DEFAULT NULL COMMENT 'Title',
  `abstract` varchar(200) DEFAULT NULL COMMENT 'Abstract',
  `content` text COMMENT 'Content',
  `creation_datetime` datetime DEFAULT NULL COMMENT 'Creation datetime',
  `modify_datetime` datetime DEFAULT NULL COMMENT 'Modify datetime',
  `author` varchar(100) DEFAULT NULL COMMENT 'Author',
  PRIMARY KEY (`id`),
  UNIQUE KEY `label_UNIQUE` (`label`),
  KEY `fk_content_1_idx` (`category_id`),
  CONSTRAINT `fk_content_1` FOREIGN KEY (`category_id`) REFERENCES `content_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8