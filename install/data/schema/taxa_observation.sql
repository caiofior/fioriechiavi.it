CREATE TABLE `taxa_observation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `taxa_id` int(11) NOT NULL,
  `profile_id` int(11) DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `position` point NOT NULL,
  `valid` smallint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `taxa_id` (`taxa_id`),
  KEY `datetime` (`title`),
  KEY `profile_id` (`profile_id`),
  SPATIAL KEY `taxa_observation_position` (`position`),
  SPATIAL KEY `position` (`position`),
  KEY `valid` (`valid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8