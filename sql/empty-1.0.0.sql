--
-- Structure de la table 'glpi_plugin_eventsmanager_events'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_events`;
CREATE TABLE `glpi_plugin_eventsmanager_events` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `priority` int(11) NOT NULL DEFAULT '1',
   `status` int(11) NOT NULL DEFAULT '1',
   `time_to_resolve` datetime default NULL,
   `action` int(11) default '1',
   `eventtype` int(11) default '0',
   `users_id` int(11) NOT NULL default '0',
   `groups_id` int(11) NOT NULL default '0',
   `comment` text collate utf8_unicode_ci,
   `date_mod` datetime default NULL,
   `is_deleted` tinyint(1) NOT NULL default '0',
   `date_creation` datetime default NULL,
   `date_assign` datetime default NULL,
   `date_close` datetime default NULL,
   `date_ticket` datetime default NULL,
   `users_assigned` int(11) NOT NULL default '0',
   `groups_assigned` int(11) NOT NULL default '0',
   `users_close` int(11) NOT NULL default '0',
   `plugin_eventsmanager_origins_id` int(11) NOT NULL default '0',
   `impact` int(11) NOT NULL DEFAULT '1',
   `reminders_id` int(11) default NULL,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `users_id` (`users_id`),
   KEY `users_assigned` (`users_assigned`),
   KEY `users_close` (`users_close`),
   KEY `groups_id` (`groups_id`),
   KEY `groups_assigned` (`groups_assigned`),
   KEY `reminders_id` (`reminders_id`),
   KEY `is_deleted` (`is_deleted`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Structure de la table 'glpi_plugin_eventsmanager_configs'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_configs`;
CREATE TABLE `glpi_plugin_eventsmanager_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `use_automatic_close`     tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_eventsmanager_configs` VALUES (1, 0);

--
-- Structure de la table 'glpi_plugin_eventsmanager_origins'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_origins`;
CREATE TABLE `glpi_plugin_eventsmanager_origins` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   `comment` text COLLATE utf8_unicode_ci,
   `date_mod` datetime DEFAULT NULL,
   `date_creation` datetime DEFAULT NULL,
   `requesttypes_id` int(11) NOT NULL default '0',
   `items_id` int(11) NOT NULL DEFAULT '0',
   `itemtype` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
   PRIMARY KEY (`id`),
   KEY `name` (`name`),
   KEY `requesttypes_id` (`requesttypes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Structure de la table 'glpi_plugin_eventsmanager_events_items'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_events_items`;
CREATE TABLE `glpi_plugin_eventsmanager_events_items` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `items_id` int(11) NOT NULL DEFAULT '0',
   `itemtype` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
   `plugin_eventsmanager_events_id` int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`),
   UNIQUE KEY `unicity` (`itemtype`,`items_id`,`plugin_eventsmanager_events_id`),
   KEY `plugin_eventsmanager_events_id` (`plugin_eventsmanager_events_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Structure de la table 'glpi_plugin_eventsmanager_tickets'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_tickets`;
CREATE TABLE `glpi_plugin_eventsmanager_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tickets_id` int(11) NOT NULL DEFAULT '0',
  `plugin_eventsmanager_events_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `plugin_eventsmanager_events_id` (`plugin_eventsmanager_events_id`),
  KEY `tickets_id` (`tickets_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Structure de la table 'glpi_plugin_eventsmanager_rssimports'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_rssimports`;
CREATE TABLE `glpi_plugin_eventsmanager_rssimports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_rssfeed_url` text COLLATE utf8_unicode_ci NOT NULL,
  `rssfeeds_id` int(11) DEFAULT '0',
  `use_with_plugin` int(11) DEFAULT '0',
  `default_impact` int(11) DEFAULT '1',
  `default_priority` int(11) DEFAULT '1',
  `default_eventtype` int(11) DEFAULT '0',
  `entities_id_import` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `rssfeeds_id` (`rssfeeds_id`),
  KEY `entities_id_import` (`entities_id_import`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Structure de la table 'glpi_plugin_eventsmanager_mailimports'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_mailimports`;
CREATE TABLE `glpi_plugin_eventsmanager_mailimports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mailcollectors_id` int(11) DEFAULT '0',
  `default_impact` int(11) DEFAULT '0',
  `default_priority` int(11) DEFAULT '0',
  `default_eventtype` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mailcollectors_id` (`mailcollectors_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

### Dump table glpi_plugin_eventsmanager_events_comments

DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_events_comments`;
CREATE TABLE `glpi_plugin_eventsmanager_events_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_eventsmanager_events_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL DEFAULT '0',
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `parent_comment_id` int(11) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `date_mod` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_displaypreferences` VALUES(NULL, 'PluginEventsmanagerEvent', 3, 5, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 11, 2, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 8, 3, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 9, 4, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 6, 5, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 19, 6, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 5, 7, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 7, 8, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 12, 9, 0);