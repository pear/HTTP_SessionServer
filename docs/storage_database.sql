CREATE DATABASE /*!32312 IF NOT EXISTS*/ `pear_http_sessionserver`;
USE `pear_http_sessionserver`;

DROP TABLE IF EXISTS `data`;
CREATE TABLE `data` (
  `sid` varchar(32) NOT NULL default '',
  `data` text NOT NULL,
  PRIMARY KEY  (`sid`)
) TYPE=MyISAM;
