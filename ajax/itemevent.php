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

include ('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

switch ($_GET['action']) {
   case 'add':
      //      if (isset($_GET['my_items']) && !empty($_GET['my_items'])) {
      //         list($_GET['itemtype'], $_GET['items_id']) = explode('_', $_GET['my_items']);
      //      }
      if (isset($_GET['items_id']) && isset($_GET['itemtype']) && !empty($_GET['items_id'])) {
         $_GET['params']['items_id'][$_GET['itemtype']][$_GET['items_id']] = $_GET['items_id'];
      }
      PluginEventsmanagerEvent_Item::itemAddForm(new PluginEventsmanagerEvent(), $_GET['params']);
      break;

   case 'delete':
      if (isset($_GET['items_id']) && isset($_GET['itemtype']) && !empty($_GET['items_id'])) {
         $deleted = true;
         if ($_GET['params']['id'] > 0) {
            $deleted = $item_ticket->deleteByCriteria(['plugin_eventsmanager_events_id' => $_GET['params']['id'],
                                                            'items_id'   => $_GET['items_id'],
                                                            'itemtype'   => $_GET['itemtype']]);
         }
         if ($deleted) {
            unset($_GET['params']['items_id'][$_GET['itemtype']][array_search($_GET['items_id'], $_GET['params']['items_id'][$_GET['itemtype']])]);
         }
         PluginEventsmanagerEvent_Item::itemAddForm(new PluginEventsmanagerEvent(), $_GET['params']);
      }

      break;
}
