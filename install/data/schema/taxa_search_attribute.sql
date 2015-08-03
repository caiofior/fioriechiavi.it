CREATE TABLE `taxa_search_attribute` (
  `taxa_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `value` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`taxa_id`,`attribute_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8