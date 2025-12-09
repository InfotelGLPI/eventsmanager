-- Add foreign key constraints that are missing from the database

-- Add foreign keys for eventsmanager_events table
ALTER TABLE `glpi_plugin_eventsmanager_events` 
ADD CONSTRAINT `fk_eventsmanager_events_users_assigned` 
FOREIGN KEY (`users_assigned`) REFERENCES `glpi_users` (`id`) ON DELETE SET DEFAULT;

ALTER TABLE `glpi_plugin_eventsmanager_events` 
ADD CONSTRAINT `fk_eventsmanager_events_users_close` 
FOREIGN KEY (`users_close`) REFERENCES `glpi_users` (`id`) ON DELETE SET DEFAULT;

ALTER TABLE `glpi_plugin_eventsmanager_events` 
ADD CONSTRAINT `fk_eventsmanager_events_groups_assigned` 
FOREIGN KEY (`groups_assigned`) REFERENCES `glpi_groups` (`id`) ON DELETE SET DEFAULT;
