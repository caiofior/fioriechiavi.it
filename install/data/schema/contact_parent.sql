CREATE TABLE `contact_parent` (
  `contact_id` int(11) NOT NULL,
  `parent_contanc_id` int(11) NOT NULL,
  PRIMARY KEY (`contact_id`,`parent_contanc_id`),
  KEY `contact_id` (`contact_id`),
  KEY `parent_contact_id` (`parent_contanc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8