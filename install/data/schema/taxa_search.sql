CREATE TABLE `taxa_search` (
  `taxa_id` int(11) NOT NULL,
  `lft` int(11) DEFAULT NULL,
  `rgt` int(11) DEFAULT NULL,
  `text` longtext CHARACTER SET latin1,
  PRIMARY KEY (`taxa_id`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8