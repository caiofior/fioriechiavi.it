CREATE TABLE `add_dico_item` (
  `dico_id` int(11) NOT NULL,
  `id` varchar(100) NOT NULL,
  `text` text NOT NULL,
  `taxa_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`,`dico_id`),
  KEY `fk_dico_item_taxa_idx` (`taxa_id`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Dicotomy item'