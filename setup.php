<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 eventsmanager plugin for GLPI
 Copyright (C) 2009-2017 by the eventsmanager Development Team.

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

define('PLUGIN_EVENTMANAGER_VERSION', '3.0.0');

if (!defined("PLUGIN_EVENTMANAGER_DIR")) {
   define("PLUGIN_EVENTMANAGER_DIR", Plugin::getPhpDir("eventsmanager"));
   define("PLUGIN_EVENTMANAGER_DIR_NOFULL", Plugin::getPhpDir("eventsmanager",false));
   define("PLUGIN_EVENTMANAGER_WEBDIR", Plugin::getWebDir("eventsmanager"));
}

// Init the hooks of the plugins -Needed
function plugin_init_eventsmanager() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['eventsmanager'] = true;
   $PLUGIN_HOOKS['change_profile']['eventsmanager'] = ['PluginEventsmanagerProfile', 'initProfile'];

   $PLUGIN_HOOKS['use_rules']['eventsmanager'] = ['RuleMailCollector'];

   Plugin::registerClass('PluginEventsmanagerTicket', ['addtabon' => ['Ticket']]);
   Plugin::registerClass('PluginEventsmanagerRssimport', ['addtabon' => ['RSSFeed']]);
   Plugin::registerClass('PluginEventsmanagerMailimport', ['addtabon' => ['MailCollector']]);

   if (Session::getLoginUserID()) {

      Plugin::registerClass('PluginEventsmanagerEvent', [
         'linkuser_types'  => true,
         'linkgroup_types' => true,
      ]);

      Plugin::registerClass('PluginEventsmanagerProfile',
                            ['addtabon' => 'Profile']);

      if (Session::haveRight("plugin_eventsmanager", UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['eventsmanager'] = 1;
         $PLUGIN_HOOKS['config_page']['eventsmanager']        = 'front/config.form.php';
      }

      if (Session::haveRight("plugin_eventsmanager", READ)) {
         $PLUGIN_HOOKS['menu_toadd']['eventsmanager'] = ['helpdesk' => 'PluginEventsmanagerMenu'];
      }

      if (class_exists('PluginMydashboardMenu')) {
         $PLUGIN_HOOKS['mydashboard']['eventsmanager'] = ["PluginEventsmanagerDashboard"];
      }

      if (Session::haveRight("plugin_eventsmanager", CREATE)) {
         $PLUGIN_HOOKS['use_massive_action']['eventsmanager'] = 1;
      }

      $PLUGIN_HOOKS['item_purge']['eventsmanager']['Ticket'] = ['PluginEventsmanagerTicket', 'cleanForTicket'];
      
      if (isset($_SESSION["glpiactiveprofile"])
             && $_SESSION["glpiactiveprofile"]["interface"] != "helpdesk") {
         $PLUGIN_HOOKS['add_javascript']['eventsmanager'][] = 'scripts/jsForAction.js.php';
      }
   }

   $plugin = new Plugin();
   if ($plugin->isActivated('mydashboard')) {
      Plugin::registerClass('PluginMydashboardAlert',
                            ['addtabon' => 'PluginEventsmanagerEvent']);
   }
}

// Get the name and the version of the plugin - Needed
/**
 * @return array
 */
function plugin_version_eventsmanager() {

   return [
      'name'           => _n('Event manager', 'Events manager', 2, 'eventsmanager'),
      'version'        => PLUGIN_EVENTMANAGER_VERSION,
      'license'        => 'GPLv2+',
      'author'         => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
      'homepage'       => 'https://github.com/InfotelGLPI/eventsmanager',
      'requirements' => [
         'glpi' => [
            'min' => '10.0',
            'max' => '11.0',
            'dev' => false
         ]
      ]
   ];
}
