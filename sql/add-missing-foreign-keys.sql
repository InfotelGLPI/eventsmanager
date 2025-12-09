-- Add missing foreign key constraints to eventsmanager plugin
-- Run this script if you see warnings about "groups_assigned" and "users_close" not being foreign key fields

-- First, ensure the columns have proper indexes (they should already exist)
-- Check if foreign keys already exist before adding them

-- Add foreign key for users_assigned (if not exists)
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'glpi_plugin_eventsmanager_events' 
    AND CONSTRAINT_NAME = 'fk_eventsmanager_events_users_assigned');

SET @sql1 = IF(@fk_exists = 0, 
    'ALTER TABLE `glpi_plugin_eventsmanager_events` ADD CONSTRAINT `fk_eventsmanager_events_users_assigned` FOREIGN KEY (`users_assigned`) REFERENCES `glpi_users` (`id`) ON DELETE SET DEFAULT',
    'SELECT "FK users_assigned already exists" AS message');
PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

-- Add foreign key for users_close (if not exists)
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'glpi_plugin_eventsmanager_events' 
    AND CONSTRAINT_NAME = 'fk_eventsmanager_events_users_close');

SET @sql2 = IF(@fk_exists = 0, 
    'ALTER TABLE `glpi_plugin_eventsmanager_events` ADD CONSTRAINT `fk_eventsmanager_events_users_close` FOREIGN KEY (`users_close`) REFERENCES `glpi_users` (`id`) ON DELETE SET DEFAULT',
    'SELECT "FK users_close already exists" AS message');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Add foreign key for groups_assigned (if not exists)
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'glpi_plugin_eventsmanager_events' 
    AND CONSTRAINT_NAME = 'fk_eventsmanager_events_groups_assigned');

SET @sql3 = IF(@fk_exists = 0, 
    'ALTER TABLE `glpi_plugin_eventsmanager_events` ADD CONSTRAINT `fk_eventsmanager_events_groups_assigned` FOREIGN KEY (`groups_assigned`) REFERENCES `glpi_groups` (`id`) ON DELETE SET DEFAULT',
    'SELECT "FK groups_assigned already exists" AS message');
PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

SELECT 'Foreign keys have been added successfully!' AS result;
