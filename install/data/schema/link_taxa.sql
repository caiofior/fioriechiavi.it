CREATE TABLE `link_taxa` (
  `provider_id` int(11) NOT NULL COMMENT 'Provider Id',
  `taxa_id` int(11) NOT NULL COMMENT 'Taxa id',
  `link` varchar(100) NOT NULL COMMENT 'Link',
  `datetime` datetime NOT NULL COMMENT 'Last ceck datetime',
  `fixed` tinyint(1) NOT NULL COMMENT 'Manual edited and fixed',
  PRIMARY KEY (`provider_id`,`taxa_id`),
  KEY `taxa` (`taxa_id`) USING BTREE,
  KEY `Provider id` (`provider_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Link to specific taxa'