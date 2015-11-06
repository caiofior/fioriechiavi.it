CREATE TABLE `taxa_observation_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id of taxa observation image',
  `taxa_observation_id` int(11) DEFAULT NULL COMMENT 'Id of taxa observation',
  `filename` varchar(200) DEFAULT NULL COMMENT 'Filename',
  PRIMARY KEY (`id`),
  KEY `fk_taxa_observation_image_1_idx` (`taxa_observation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Taxa observation images'