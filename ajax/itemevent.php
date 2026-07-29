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
use Glpi\Exception\Http\BadRequestHttpException;
use GlpiPlugin\Eventsmanager\Event;
use GlpiPlugin\Eventsmanager\Event_Item;

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();
// The 'delete' branch below mutates state (deleteByCriteria). GLPI 11's CheckCsrfListener
// only validates non-GET requests, so require POST for the whole endpoint to keep the
// mutating action behind CSRF protection (the caller sends POST; core adds the token
// header automatically). Reading from $_POST also prevents GET-based CSRF via <img>.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new BadRequestHttpException();
}
// checkLoginUser() performs no authorization on GLPI 11. Both branches below are part
// of the event's item-association edit workflow, so require the plugin UPDATE right —
// consistent with the sibling endpoints (adduser/closeevent) and with the 'delete'
// branch, which additionally enforces can(UPDATE) on the parent event.
Session::checkRight('plugin_eventsmanager', UPDATE);
$item_ticket = new Event_Item();
switch ($_POST['action'] ?? '') {
   case 'add':
      //      if (isset($_POST['my_items']) && !empty($_POST['my_items'])) {
      //         list($_POST['itemtype'], $_POST['items_id']) = explode('_', $_POST['my_items']);
      //      }
      if (isset($_POST['items_id']) && isset($_POST['itemtype']) && !empty($_POST['items_id'])) {
         $_POST['params']['items_id'][$_POST['itemtype']][$_POST['items_id']] = $_POST['items_id'];
      }
      Event_Item::itemAddForm(new Event(), $_POST['params']);
      break;

   case 'delete':
      if (isset($_POST['items_id']) && isset($_POST['itemtype']) && !empty($_POST['items_id'])) {
         $deleted   = true;
         $events_id = (int) ($_POST['params']['id'] ?? 0);
         if ($events_id > 0) {
            // Enforce right + entity access on the parent event before deleting any
            // association (defends against IDOR / broken access control).
            $event = new Event();
            if (!$event->can($events_id, UPDATE)) {
                throw new AccessDeniedHttpException();
            }
            $deleted = $item_ticket->deleteByCriteria(['plugin_eventsmanager_events_id' => $events_id,
                                                            'items_id'   => (int) $_POST['items_id'],
                                                            'itemtype'   => $_POST['itemtype']]);
         }
         if ($deleted) {
            unset($_POST['params']['items_id'][$_POST['itemtype']][array_search($_POST['items_id'], $_POST['params']['items_id'][$_POST['itemtype']])]);
         }
         Event_Item::itemAddForm(new Event(), $_POST['params']);
      }

      break;
}
