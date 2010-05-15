USE pic;

DROP TABLE IF EXISTS `pic`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `pic` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `id_key` varchar(128) NOT NULL,
    `delete_key` varchar(128) NOT NULL,
    `uploaded` datetime NOT NULL,
    `location` varchar(128) NOT NULL,
    `storage` varchar(8) NOT NULL,
    `filename` text,
    `hash_filename` varchar(128),
    `size` bigint(20) default NULL,
    `width` bigint(20) NOT NULL,
    `height` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;


