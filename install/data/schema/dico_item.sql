CREATE TABLE `dico_item` (
  `id` varchar(100) NOT NULL,
  `parent_taxa_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `taxa_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`,`parent_taxa_id`),
  KEY `fk_dico_item_taxa_idx` (`taxa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Dicotomy item'