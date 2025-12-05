UPDATE `glpi_displaypreferences` SET `itemtype` = 'GlpiPlugin\\Eventsmanager\\Event' WHERE `itemtype` = 'PluginEventsmanagerEvent';
DELETE FROM `glpi_crontasks` WHERE `itemtype` LIKE 'PluginEventsmanager%';
