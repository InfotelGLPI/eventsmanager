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

-- Add foreign key constraints if they don't exist
SET @constraint_check = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'glpi_plugin_eventsmanager_events' 
    AND CONSTRAINT_NAME = 'fk_eventsmanager_events_users_assigned');

SET @sql_fk1 = IF(@constraint_check = 0, 
    'ALTER TABLE `glpi_plugin_eventsmanager_events` ADD CONSTRAINT `fk_eventsmanager_events_users_assigned` FOREIGN KEY (`users_assigned`) REFERENCES `glpi_users` (`id`) ON DELETE SET DEFAULT',
    'SELECT "Foreign key fk_eventsmanager_events_users_assigned already exists"');
PREPARE stmt FROM @sql_fk1;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_check = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'glpi_plugin_eventsmanager_events' 
    AND CONSTRAINT_NAME = 'fk_eventsmanager_events_users_close');

SET @sql_fk2 = IF(@constraint_check = 0, 
    'ALTER TABLE `glpi_plugin_eventsmanager_events` ADD CONSTRAINT `fk_eventsmanager_events_users_close` FOREIGN KEY (`users_close`) REFERENCES `glpi_users` (`id`) ON DELETE SET DEFAULT',
    'SELECT "Foreign key fk_eventsmanager_events_users_close already exists"');
PREPARE stmt FROM @sql_fk2;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_check = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'glpi_plugin_eventsmanager_events' 
    AND CONSTRAINT_NAME = 'fk_eventsmanager_events_groups_assigned');

SET @sql_fk3 = IF(@constraint_check = 0, 
    'ALTER TABLE `glpi_plugin_eventsmanager_events` ADD CONSTRAINT `fk_eventsmanager_events_groups_assigned` FOREIGN KEY (`groups_assigned`) REFERENCES `glpi_groups` (`id`) ON DELETE SET DEFAULT',
    'SELECT "Foreign key fk_eventsmanager_events_groups_assigned already exists"');
PREPARE stmt FROM @sql_fk3;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

