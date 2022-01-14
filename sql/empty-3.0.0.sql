--
-- Structure de la table 'glpi_plugin_eventsmanager_events'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_events`;
CREATE TABLE `glpi_plugin_eventsmanager_events` (
   `id` int unsigned NOT NULL auto_increment,
   `entities_id` int unsigned NOT NULL default '0',
   `is_recursive` tinyint NOT NULL default '0',
   `name` varchar(255) collate utf8mb4_unicode_ci default NULL,
   `priority` int unsigned NOT NULL DEFAULT '1',
   `status` int unsigned NOT NULL DEFAULT '1',
   `time_to_resolve`  timestamp NULL default '0000-00-00 00:00:00',
   `action` int unsigned default '1',
   `eventtype` int unsigned default '0',
   `users_id` int unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `groups_id` int unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
   `comment` text collate utf8mb4_unicode_ci,
   `date_mod` timestamp NULL default '0000-00-00 00:00:00',
   `is_deleted` tinyint NOT NULL default '0',
   `date_creation` timestamp NULL default '0000-00-00 00:00:00',
   `date_assign` timestamp NULL default '0000-00-00 00:00:00',
   `date_close` timestamp NULL default '0000-00-00 00:00:00',
   `date_ticket` timestamp NULL default '0000-00-00 00:00:00',
   `users_assigned` int unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `groups_assigned` int unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
   `users_close` int unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `plugin_eventsmanager_origins_id` int unsigned NOT NULL default '0',
   `impact` int unsigned NOT NULL DEFAULT '1',
   `reminders_id` int unsigned default NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Structure de la table 'glpi_plugin_eventsmanager_configs'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_configs`;
CREATE TABLE `glpi_plugin_eventsmanager_configs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `use_automatic_close`     tinyint NOT NULL default '0',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_plugin_eventsmanager_configs` VALUES (1, 0);

--
-- Structure de la table 'glpi_plugin_eventsmanager_origins'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_origins`;
CREATE TABLE `glpi_plugin_eventsmanager_origins` (
   `id` int unsigned NOT NULL AUTO_INCREMENT,
   `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `comment` text COLLATE utf8mb4_unicode_ci,
   `date_mod` timestamp NULL default '0000-00-00 00:00:00',
   `date_creation` timestamp NULL default '0000-00-00 00:00:00',
   `requesttypes_id` int unsigned NOT NULL default '0',
   `items_id` int unsigned NOT NULL DEFAULT '0',
   `itemtype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
   PRIMARY KEY (`id`),
   KEY `name` (`name`),
   KEY `requesttypes_id` (`requesttypes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Structure de la table 'glpi_plugin_eventsmanager_events_items'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_events_items`;
CREATE TABLE `glpi_plugin_eventsmanager_events_items` (
   `id` int unsigned NOT NULL AUTO_INCREMENT,
   `items_id` int unsigned NOT NULL DEFAULT '0',
   `itemtype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
   `plugin_eventsmanager_events_id` int unsigned NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`),
   UNIQUE KEY `unicity` (`itemtype`,`items_id`,`plugin_eventsmanager_events_id`),
   KEY `plugin_eventsmanager_events_id` (`plugin_eventsmanager_events_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Structure de la table 'glpi_plugin_eventsmanager_tickets'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_tickets`;
CREATE TABLE `glpi_plugin_eventsmanager_tickets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tickets_id` int unsigned NOT NULL DEFAULT '0',
  `plugin_eventsmanager_events_id` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `plugin_eventsmanager_events_id` (`plugin_eventsmanager_events_id`),
  KEY `tickets_id` (`tickets_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Structure de la table 'glpi_plugin_eventsmanager_rssimports'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_rssimports`;
CREATE TABLE `glpi_plugin_eventsmanager_rssimports` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `last_rssfeed_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rssfeeds_id` int unsigned DEFAULT '0',
  `use_with_plugin` int unsigned DEFAULT '0',
  `default_impact` int unsigned DEFAULT '1',
  `default_priority` int unsigned DEFAULT '1',
  `default_eventtype` int unsigned DEFAULT '0',
  `entities_id_import` int unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `rssfeeds_id` (`rssfeeds_id`),
  KEY `entities_id_import` (`entities_id_import`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Structure de la table 'glpi_plugin_eventsmanager_mailimports'
--
DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_mailimports`;
CREATE TABLE `glpi_plugin_eventsmanager_mailimports` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `mailcollectors_id` int unsigned DEFAULT '0',
  `default_impact` int unsigned DEFAULT '0',
  `default_priority` int unsigned DEFAULT '0',
  `default_eventtype` int unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mailcollectors_id` (`mailcollectors_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

### Dump table glpi_plugin_eventsmanager_events_comments

DROP TABLE IF EXISTS `glpi_plugin_eventsmanager_events_comments`;
CREATE TABLE `glpi_plugin_eventsmanager_events_comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `plugin_eventsmanager_events_id` int unsigned NOT NULL,
  `users_id` int unsigned NOT NULL DEFAULT '0',
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_comment_id` int unsigned DEFAULT NULL,
  `date_creation` timestamp NULL default '0000-00-00 00:00:00',
  `date_mod` timestamp NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_displaypreferences` VALUES(NULL, 'PluginEventsmanagerEvent', 3, 5, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 11, 2, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 8, 3, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 9, 4, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 6, 5, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 19, 6, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 5, 7, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 7, 8, 0),
                                            (NULL, 'PluginEventsmanagerEvent', 12, 9, 0);
