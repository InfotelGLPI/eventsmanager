ALTER TABLE `glpi_plugin_eventsmanager_events` CHANGE `date_mod` `date_mod` TIMESTAMP NULL default NULL;
ALTER TABLE `glpi_plugin_eventsmanager_events` CHANGE `date_creation` `date_creation` TIMESTAMP NULL default NULL;
ALTER TABLE `glpi_plugin_eventsmanager_events` CHANGE `date_assign` `date_assign` TIMESTAMP NULL default NULL;
ALTER TABLE `glpi_plugin_eventsmanager_events` CHANGE `date_close` `date_close` TIMESTAMP NULL default NULL;
ALTER TABLE `glpi_plugin_eventsmanager_events` CHANGE `date_ticket` `date_ticket` TIMESTAMP NULL default NULL;
ALTER TABLE `glpi_plugin_eventsmanager_events` CHANGE `time_to_resolve` `time_to_resolve` TIMESTAMP NULL default NULL;

ALTER TABLE `glpi_plugin_eventsmanager_origins` CHANGE `date_mod` `date_mod` TIMESTAMP NULL default NULL;
ALTER TABLE `glpi_plugin_eventsmanager_origins` CHANGE `date_creation` `date_creation` TIMESTAMP NULL default NULL;

ALTER TABLE `glpi_plugin_eventsmanager_events_comments` CHANGE `date_creation` `date_creation` TIMESTAMP NULL default NULL;
ALTER TABLE `glpi_plugin_eventsmanager_events_comments` CHANGE `date_mod` `date_mod` TIMESTAMP NULL default NULL;
