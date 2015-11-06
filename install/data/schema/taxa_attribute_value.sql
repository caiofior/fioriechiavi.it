CREATE TABLE `taxa_attribute_value` (
  `taxa_id` int(11) NOT NULL COMMENT 'Id of taxa',
  `taxa_attribute_id` int(11) NOT NULL COMMENT 'Id of taxa attribute',
  `value` varchar(100) DEFAULT NULL COMMENT 'Value',
  PRIMARY KEY (`taxa_id`,`taxa_attribute_id`),
  KEY `fk_taxa_attribute_value_taxa_id` (`taxa_id`),
  KEY `fk_taxa_attribute_value_taxa_attribute` (`taxa_attribute_id`),
  KEY `taxa_attribute_value` (`value`),
  CONSTRAINT `fk_taxa_attribute_value_1` FOREIGN KEY (`taxa_id`) REFERENCES `taxa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_taxa_attribute_value_2` FOREIGN KEY (`taxa_attribute_id`) REFERENCES `taxa_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Taxa attribute value'