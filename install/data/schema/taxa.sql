CREATE TABLE `taxa` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id of taxonomy',
  `taxa_kind_id` int(11) DEFAULT NULL COMMENT 'Taxonomy kind',
  `name` varchar(100) DEFAULT NULL COMMENT 'Taxonomy name',
  `description` text COMMENT 'Taxonomi description',
  `creation_datetime` datetime DEFAULT NULL COMMENT 'Creation datetime',
  `change_datetime` datetime DEFAULT NULL COMMENT 'Last change datetime',
  `col_id` varchar(40) DEFAULT NULL COMMENT 'Id in Catalog of life database',
  `eol_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Id of taxa in Enciclopedia of life',
  PRIMARY KEY (`id`),
  KEY `fk_taxonomy_kind_idx` (`taxa_kind_id`),
  KEY `modidy_datetime` (`change_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Taxa'