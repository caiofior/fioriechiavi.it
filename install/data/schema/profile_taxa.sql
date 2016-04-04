CREATE TABLE `profile_taxa` (
  `profile_id` int(11) NOT NULL COMMENT 'profile enabled for editing',
  `taxa_id` int(11) NOT NULL COMMENT 'taxa editable',
  UNIQUE KEY `profile_taxa` (`profile_id`,`taxa_id`),
  KEY `profile_id` (`profile_id`),
  KEY `taxa_id` (`taxa_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin