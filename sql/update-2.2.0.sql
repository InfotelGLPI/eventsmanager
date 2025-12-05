ALTER TABLE `glpi_plugin_eventsmanager_events` CHANGE `date_mod` `date_mod` timestamp NULL DEFAULT NULL;
ALTER TABLE `glpi_plugin_eventsmanager_events` CHANGE `date_creation` `date_creation` timestamp NULL DEFAULT NULL;
ALTER TABLE `glpi_plugin_eventsmanager_events` CHANGE `date_assign` `date_assign` timestamp NULL DEFAULT NULL;
ALTER TABLE `glpi_plugin_eventsmanager_events` CHANGE `date_close` `date_close` timestamp NULL DEFAULT NULL;
ALTER TABLE `glpi_plugin_eventsmanager_events` CHANGE `date_ticket` `date_ticket` timestamp NULL DEFAULT NULL;
ALTER TABLE `glpi_plugin_eventsmanager_events` CHANGE `time_to_resolve` `time_to_resolve` timestamp NULL DEFAULT NULL;

ALTER TABLE `glpi_plugin_eventsmanager_origins` CHANGE `date_mod` `date_mod` timestamp NULL DEFAULT NULL;
ALTER TABLE `glpi_plugin_eventsmanager_origins` CHANGE `date_creation` `date_creation` timestamp NULL DEFAULT NULL;

ALTER TABLE `glpi_plugin_eventsmanager_events_comments` CHANGE `date_creation` `date_creation` timestamp NULL DEFAULT NULL;
ALTER TABLE `glpi_plugin_eventsmanager_events_comments` CHANGE `date_mod` `date_mod` timestamp NULL DEFAULT NULL;
