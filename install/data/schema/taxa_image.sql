CREATE TABLE `taxa_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id of taxa image',
  `taxa_id` int(11) DEFAULT NULL COMMENT 'Id of taxa',
  `filename` varchar(200) DEFAULT NULL COMMENT 'Filename',
  PRIMARY KEY (`id`),
  KEY `fk_taxa_image_1_idx` (`taxa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Taxa images'