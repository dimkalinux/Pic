USE pic2;

/*DROP TABLE IF EXISTS `pic`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `pic` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `group_id` varchar(128) NOT NULL,
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
    `p_width` bigint(20) NOT NULL,
    `p_height` bigint(20) NOT NULL,
    `p_size` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
*/

DROP TABLE IF EXISTS `users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `users` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `email` varchar(256) default NULL,
    `password` text NOT NULL,
    `regdate` datetime NOT NULL,
    `admin` tinyint(1) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;


DROP TABLE IF EXISTS `session`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `session` (
  `sid` varchar(40) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `expire` datetime NOT NULL,
  `email` varchar(129) default NULL,
  `admin` tinyint(1) default '0',
  KEY `sid` (`sid`,`uid`,`ip`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;


ALTER TABLE pic ADD COLUMN owner_id int(10) unsigned NOT NULL AFTER 'p_size';


DROP TABLE IF EXISTS `users_config`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `users_config` (
    `uid` int(10) unsigned NOT NULL,
    `name` varchar(128) NOT NULL,
    `val` varchar(128) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
