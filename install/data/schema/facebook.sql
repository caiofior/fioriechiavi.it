CREATE TABLE `facebook` (
  `userID` varchar(20) CHARACTER SET latin1 NOT NULL,
  `accessToken` text CHARACTER SET latin1,
  `signedRequest` text CHARACTER SET latin1,
  `creation_datetime` datetime DEFAULT NULL,
  `last_login_datetime` datetime DEFAULT NULL,
  `expires_datetime` datetime DEFAULT NULL,
  `profile_id` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`userID`),
  KEY `profile` (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8