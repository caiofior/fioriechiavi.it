CREATE TABLE `taxa_region` (
  `taxa_id` int(11) NOT NULL,
  `region_id` varchar(20) NOT NULL,
  PRIMARY KEY (`taxa_id`,`region_id`),
  KEY `fk_taxa_region_region_idx` (`region_id`),
  KEY `fk_taxa_region_taxa_idx` (`taxa_id`),
  CONSTRAINT `fk_taxa_region_region` FOREIGN KEY (`region_id`) REFERENCES `region` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Association between taxa and region'