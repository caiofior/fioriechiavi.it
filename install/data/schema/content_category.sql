CREATE TABLE `content_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'category id',
  `name` varchar(200) DEFAULT NULL COMMENT 'Category name',
  `description` text COMMENT 'category description',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8