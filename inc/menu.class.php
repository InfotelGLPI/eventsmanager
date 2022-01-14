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

class PluginEventsmanagerMenu extends CommonGLPI {

   static $rightname = 'plugin_eventsmanager';

   /**
    * @param int $nb
    *
    * @return translated
    */
   static function getMenuName($nb = 1) {
      return _n('Event manager', 'Events manager', $nb, 'eventsmanager');
   }

   /**
    * @return array
    */
   static function getMenuContent() {

      $menu                       = [];
      $menu['title']              = self::getMenuName(2);
      $menu['page']               = PluginEventsmanagerEvent::getSearchURL(false);
      $menu['links']['search']    = PluginEventsmanagerEvent::getSearchURL(false);
      if (PluginEventsmanagerEvent::canCreate()) {
         $menu['links']['add']    = PluginEventsmanagerEvent::getFormURL(false);
      }
      if (Config::canView()) {
         $menu['links']['config'] = PluginEventsmanagerConfig::getFormURL(false);
      }
      $menu['icon']               = PluginEventsmanagerEvent::getIcon();

      return $menu;
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['helpdesk']['types']['PluginEventsmanagerMenu'])) {
         unset($_SESSION['glpimenu']['helpdesk']['types']['PluginEventsmanagerMenu']);
      }
      if (isset($_SESSION['glpimenu']['helpdesk']['content']['plugineventsmanagermenu'])) {
         unset($_SESSION['glpimenu']['helpdesk']['content']['plugineventsmanagermenu']);
      }
   }
}
