CREATE TABLE `taxa_kind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ord` int(11) DEFAULT NULL,
  `initials` varchar(5) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `order` (`ord`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Taxa kind'