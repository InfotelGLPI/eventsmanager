<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 eventsmanager plugin for GLPI
 Copyright (C) 2017-2022 by the eventsmanager Development Team.

 https://github.com/InfotelGLPI/eventsmanager
 -------------------------------------------------------------------------

 LICENSE

 This file is part of eventsmanager.

 eventsmanager is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 eventsmanager is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with eventsmanager. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * @return bool
 */
function plugin_eventsmanager_install() {
   global $DB;

   include_once(PLUGIN_EVENTMANAGER_DIR . "/inc/profile.class.php");
   $update = true;

   if (!$DB->tableExists("glpi_plugin_eventsmanager_events")) {
      $update = false;
      $DB->runFile(PLUGIN_EVENTMANAGER_DIR . "/sql/empty-3.0.0.sql");
   }

   if ($update) {
      $DB->runFile(PLUGIN_EVENTMANAGER_DIR . "/sql/update-2.2.0.sql");
   }


   PluginEventsmanagerProfile::initProfile();
   PluginEventsmanagerProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   CronTask::Register('PluginEventsmanagerRssimport', 'RssImport', DAY_TIMESTAMP);

   return true;
}

/**
 * @return bool
 */
function plugin_eventsmanager_uninstall() {
   global $DB;

   include_once(PLUGIN_EVENTMANAGER_DIR . "/inc/profile.class.php");

   $tables = [
      "glpi_plugin_eventsmanager_events",
      "glpi_plugin_eventmanager_eventtypes",
      "glpi_plugin_eventsmanager_rssimports",
      "glpi_plugin_eventsmanager_tickets",
      "glpi_plugin_eventsmanager_origins",
      "glpi_plugin_eventsmanager_events_items",
      "glpi_plugin_eventsmanager_configs",
      "glpi_plugin_eventsmanager_events_comments",
      "glpi_plugin_eventsmanager_mailimports"];

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   $tables_glpi = ["glpi_displaypreferences",
                        "glpi_notepads",
                        "glpi_documents_items",
                        "glpi_savedsearches",
                        "glpi_logs"];

   foreach ($tables_glpi as $table_glpi) {
      $DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` LIKE 'PluginEventsmanagerEvent%';");
   }

   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginEventsmanagerProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(['name' => $right['field']]);
   }
   PluginEventsmanagerEvent::removeRightsFromSession();

   PluginEventsmanagerProfile::removeRightsFromSession();

   return true;
}

// Define dropdown relations
/**
 * @return array
 */
function plugin_eventsmanager_getDatabaseRelations() {

   if (Plugin::isPluginActive("eventsmanager")) {
      return ["glpi_users"          => ["glpi_plugin_eventsmanager_events" => "users_id",
                                                  "glpi_plugin_eventsmanager_events" => "users_assigned",
                                                  "glpi_plugin_eventsmanager_events" => "users_close"],
                   "glpi_groups"         => ["glpi_plugin_eventsmanager_events" => "groups_id",
                                                  "glpi_plugin_eventsmanager_events" => "groups_assigned"],
                   "glpi_entities"       => ["glpi_plugin_eventsmanager_events"     => "entities_id",
                                                  "glpi_plugin_eventsmanager_rssimports" => "entities_id_import"],
                   "glpi_reminders"      => ["glpi_plugin_eventsmanager_events" => "reminders_id"],
                   "glpi_requesttypes"   => ["glpi_plugin_eventsmanager_origins" => "requesttypes_id"],
                   "glpi_tickets"        => ["glpi_plugin_eventsmanager_tickets" => "tickets_id"],
                   "glpi_rssfeeds"       => ["glpi_plugin_eventsmanager_rssimports" => "rssfeeds_id"],
                   "glpi_mailcollectors" => ["glpi_plugin_eventsmanager_mailimports" => "mailcollectors_id"]];
   } else {
      return [];
   }
}

// Define Dropdown tables to be manage in GLPI :
/**
 * @return array
 */
function plugin_eventsmanager_getDropdown() {

   if (Plugin::isPluginActive("eventsmanager")) {
      return ['PluginEventsmanagerOrigin' => PluginEventsmanagerOrigin::getTypeName(2)];
   } else {
      return [];
   }
}

function plugin_eventsmanager_getAddSearchOptions($itemtype) {

   $sopt = [];

   if ($itemtype == 'RSSFeed') {
      if (Session::haveRight("plugin_eventsmanager", READ)) {
         $sopt = PluginEventsmanagerRssimport::addSearchOptions($sopt);
      }
   }
   return $sopt;
}

/**
 * @param $type
 * @param $ID
 * @param $data
 * @param $num
 *
 * @return string
 */
function plugin_eventsmanager_displayConfigItem($type, $ID, $data, $num) {

   $searchopt =& Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   switch ($table . '.' . $field) {
      case "glpi_plugin_eventsmanager_events.priority" :
         return " style=\"background-color:" . $_SESSION["glpipriority_" . $data[$num][0]['name']] . ";\" ";
         break;
      case "glpi_plugin_eventsmanager_events.eventtype" :
         return ' style="' . PluginEventsmanagerEvent::getTypeColor($data[$num][0]['name']) . ';"';
         break;
      case "glpi_plugin_eventsmanager_events.action" :
         return ' style="min-width:100px;"';
         break;
   }
   return "";
}

/**
 * @param $options
 *
 * @return array
 */
function plugin_eventsmanager_getRuleActions($options) {
   $event = new PluginEventsmanagerEvent();
   return $event->getActions();
}

/**
 * @param $options
 *
 * @return mixed
 */
function plugin_eventsmanager_getRuleCriterias($options) {
   $event = new PluginEventsmanagerEvent();
   return $event->getCriterias();
}

/**
 * @param $options
 *
 * @return the
 */
function plugin_eventsmanager_executeActions($options) {
   $event = new PluginEventsmanagerEvent();
   return $event->executeActions($options['action'], $options['output'], $options['params']);
}
