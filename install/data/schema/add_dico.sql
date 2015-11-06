CREATE TABLE `add_dico` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id',
  `taxa_id` int(11) DEFAULT NULL COMMENT 'Taxa id',
  `name` varchar(200) DEFAULT NULL COMMENT 'Name',
  PRIMARY KEY (`id`),
  KEY `taxa_id` (`taxa_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Additional dico'