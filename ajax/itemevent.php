<?php

/*
 -------------------------------------------------------------------------
 eventsmanager plugin for GLPI
 Copyright (C) 2017-2026 by the eventsmanager Development Team.

 https://github.com/InfotelGLPI/eventsmanager
 -------------------------------------------------------------------------

 LICENSE

 This file is part of eventsmanager.

 eventsmanager is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 eventsmanager is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with eventsmanager. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Eventsmanager\Event;
use GlpiPlugin\Eventsmanager\Event_Item;

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();
$item_ticket = new Event_Item();
switch ($_GET['action']) {
   case 'add':
      //      if (isset($_GET['my_items']) && !empty($_GET['my_items'])) {
      //         list($_GET['itemtype'], $_GET['items_id']) = explode('_', $_GET['my_items']);
      //      }
      if (isset($_GET['items_id']) && isset($_GET['itemtype']) && !empty($_GET['items_id'])) {
         $_GET['params']['items_id'][$_GET['itemtype']][$_GET['items_id']] = $_GET['items_id'];
      }
      Event_Item::itemAddForm(new Event(), $_GET['params']);
      break;

   case 'delete':
      if (isset($_GET['items_id']) && isset($_GET['itemtype']) && !empty($_GET['items_id'])) {
         $deleted   = true;
         $events_id = (int) ($_GET['params']['id'] ?? 0);
         if ($events_id > 0) {
            // Enforce right + entity access on the parent event before deleting any
            // association (defends against IDOR / broken access control).
            $event = new Event();
            if (!$event->can($events_id, UPDATE)) {
                throw new AccessDeniedHttpException();
            }
            $deleted = $item_ticket->deleteByCriteria(['plugin_eventsmanager_events_id' => $events_id,
                                                            'items_id'   => (int) $_GET['items_id'],
                                                            'itemtype'   => $_GET['itemtype']]);
         }
         if ($deleted) {
            unset($_GET['params']['items_id'][$_GET['itemtype']][array_search($_GET['items_id'], $_GET['params']['items_id'][$_GET['itemtype']])]);
         }
         Event_Item::itemAddForm(new Event(), $_GET['params']);
      }

      break;
}
