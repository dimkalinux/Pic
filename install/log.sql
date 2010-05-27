use pic2;

DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs`
(
	`id` int(10) unsigned NOT NULL auto_increment,
   	`date` datetime NOT NULL,
	`type` enum('debug','info','warn','error') NOT NULL,
   	`message` blob NOT NULL,
   	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
