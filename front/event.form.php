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

include('../../../inc/includes.php');

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$event = new PluginEventsmanagerEvent();

if (isset($_POST["add"])) {
   $event->check(-1, CREATE, $_POST);
   $newID = $event->add($_POST);
   if ($_SESSION['glpibackcreated']) {
      Html::redirect($event->getFormURL() . "?id=" . $newID);
   }
   Html::back();
} else if (isset($_POST["update"])) {
   $event->check($_POST['id'], UPDATE);
   $event->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $event->check($_POST['id'], DELETE);
   $event->delete($_POST);
   $event->redirectToList();

} else if (isset($_POST["restore"])) {
   $event->check($_POST['id'], PURGE);
   $event->restore($_POST);
   $event->redirectToList();

} else if (isset($_POST["purge"])) {
   $event->check($_POST['id'], PURGE);
   $event->delete($_POST, 1);
   $event->redirectToList();

} else if (isset($_POST["ticket_link"])) {

   $ticket = new PluginEventsmanagerTicket();
   $event  = new PluginEventsmanagerEvent();
   $event->check($_POST['plugin_eventsmanager_events_id'], UPDATE);
   //   $_POST['id'] = PluginEventsmanagerEvent::CLOSED_STATE;
   //      $_POST['status'] = $_POST['plugin_eventsmanager_events_id'];
   //      $event->update($_POST);
   $ticket->add(['tickets_id'                     => $_POST['tickets_id'],
                      'plugin_eventsmanager_events_id' => $_POST['plugin_eventsmanager_events_id']]);
   Html::back();

} else if (isset($_POST["assign"])) {
   $event->check($_POST['id'], UPDATE);
   $_POST['status'] = PluginEventsmanagerEvent::ASSIGNED_STATE;
   $event->update($_POST);
   Html::back();

} else {

   $event->checkGlobal(READ);

   Html::header(PluginEventsmanagerEvent::getTypeName(2), '', "helpdesk", "plugineventsmanagermenu");

   $event->display($_GET);

   Html::footer();
}
