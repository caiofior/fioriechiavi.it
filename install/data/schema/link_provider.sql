CREATE TABLE `link_provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id',
  `name` varchar(50) NOT NULL COMMENT 'Provider name',
  `priority` int(11) NOT NULL COMMENT 'Priority',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `priority` (`priority`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8