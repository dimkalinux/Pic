--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `feedback` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`message` blob NOT NULL,
	`email` varchar(255) default NULL,
	`ip` int(10) unsigned NOT NULL,
	`date` datetime NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
