USE portal;

DROP TABLE IF EXISTS `data`;
CREATE TABLE `data`
(
 	`source_id` tinyint unsigned NOT NULL,
 	`id` int(10) unsigned NOT NULL,
 	`full_id` varchar(16) NOT NULL,
	`url` varchar(255) NOT NULL,
	`img` varchar(255),
	`ename` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`rname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
	`size` int(10) unsigned NOT NULL DEFAULT 0,
	`torrent` BOOL DEFAULT 0,
	`seed` int(10) unsigned DEFAULT 0,
	`leech` int(10) unsigned DEFAULT 0,
	`se` int(10) unsigned DEFAULT 0,
	`ep` int(10) unsigned DEFAULT 0,
	`desc` TEXT DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE utf8_general_ci;


/*
DROP TABLE IF EXISTS `sources`;
CREATE TABLE `sources`
(
 	`id` int(10) unsigned NOT NULL auto_increment,
 	`source_id` int(10) unsigned NOT NULL,
	`url` varchar(255) NOT NULL,
	`disabled` BOOL DEFAULT 0,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;
*/