CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime` datetime DEFAULT NULL,
  `profile_id` int(11) DEFAULT NULL,
  `email` varchar(80) CHARACTER SET latin1 DEFAULT NULL,
  `url` text CHARACTER SET latin1,
  `action` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  `label` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`),
  KEY `email` (`email`),
  KEY `datetime` (`datetime`),
  KEY `action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8