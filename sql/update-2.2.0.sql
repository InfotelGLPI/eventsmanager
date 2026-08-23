--
-- -------------------------------------------------------------------------
-- eventsmanager plugin for GLPI
-- Copyright (C) 2017-2026 by the eventsmanager Development Team.
--
-- https://github.com/InfotelGLPI/eventsmanager
-- -------------------------------------------------------------------------
--
-- LICENSE
--
-- This file is part of eventsmanager.
--
-- eventsmanager is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- eventsmanager is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with eventsmanager. If not, see <http://www.gnu.org/licenses/>.
-- --------------------------------------------------------------------------
--

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
