CREATE TABLE `contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime` datetime DEFAULT NULL,
  `ip` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `name` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `message` text CHARACTER SET latin1,
  `mail` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `fax` varchar(50) CHARACTER SET latin1 DEFAULT NULL COMMENT 'Contant site',
  `from_id` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `to_id` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`) USING BTREE,
  KEY `datetime` (`datetime`) USING BTREE,
  KEY `from_id` (`from_id`),
  KEY `to_id` (`to_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8