CREATE TABLE `facebook_graph` (
  `userID` varchar(20) CHARACTER SET latin1 NOT NULL,
  `label` varchar(50) CHARACTER SET latin1 NOT NULL,
  `value` text CHARACTER SET latin1,
  `last_update_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`userID`,`label`),
  CONSTRAINT `fk_facebook_graph_1` FOREIGN KEY (`userID`) REFERENCES `facebook` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8