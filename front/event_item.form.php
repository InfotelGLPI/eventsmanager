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

Session ::checkLoginUser();

$item = new PluginEventsmanagerEvent_Item();

if (isset($_POST["add"])) {
   if (isset($_POST['my_items']) && !empty($_POST['my_items'])) {
      list($_POST['itemtype'], $_POST['items_id']) = explode('_', $_POST['my_items']);
   }

   if (isset($_POST['add_items_id'])) {
      $_POST['items_id'] = $_POST['add_items_id'];
   }

   if (!isset($_POST['items_id']) || empty($_POST['items_id'])) {
      $message = sprintf(__('Mandatory fields are not filled. Please correct: %s'),
                         _n('Associated element', 'Associated elements', 1));
      Session::addMessageAfterRedirect($message, false, ERROR);
      Html::back();
   }

   $item->check(-1, CREATE, $_POST);

   $item->add($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $item_ticket = new PluginEventsmanagerEvent_Item();
   $deleted = $item_ticket->deleteByCriteria(['plugin_eventsmanager_events_id' => $_POST['plugin_eventsmanager_events_id'],
                                                   'items_id'   => $_POST['items_id'],
                                                   'itemtype'   => $_POST['itemtype']]);
   Html::back();
}

Html::displayErrorAndDie("lost");
