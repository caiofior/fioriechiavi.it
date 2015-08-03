CREATE TABLE `login` (
  `username` varchar(100) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `profile_id` int(11) DEFAULT NULL,
  `creation_datetime` datetime DEFAULT NULL COMMENT 'user creation datetime',
  `change_datetime` datetime DEFAULT NULL COMMENT 'user last modify date time',
  `confirm_datetime` datetime DEFAULT NULL COMMENT 'confirm datet time',
  `last_login_datetime` datetime DEFAULT NULL,
  `confirm_code` varchar(50) DEFAULT NULL COMMENT 'confirm code',
  `new_username` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User data'