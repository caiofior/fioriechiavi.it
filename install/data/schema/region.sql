CREATE TABLE `region` (
  `id` varchar(20) NOT NULL COMMENT 'Region id',
  `name` varchar(100) DEFAULT NULL COMMENT 'Region description',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Region description'