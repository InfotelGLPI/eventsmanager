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
use GlpiPlugin\Eventsmanager\Event_Comment;
use GlpiPlugin\Eventsmanager\Event;

Session::checkLoginUser();

$comment = new Event_Comment();
if (!isset($_POST['plugin_eventsmanager_events_id'])) {
   $message = __('Mandatory fields are not filled!');
   Session::addMessageAfterRedirect($message, false, ERROR);
   Html::back();
}
$events_id = (int) $_POST['plugin_eventsmanager_events_id'];
$event = new Event();
// Enforce the plugin right + entity access on the parent event before any comment
// mutation (checkLoginUser above only checks authentication, not authorization).
if (!$event->can($events_id, UPDATE)) {
    throw new AccessDeniedHttpException();
}

if (isset($_POST["add"])) {
   if (!isset($_POST['plugin_eventsmanager_events_id']) || !isset($_POST['comment'])) {
      $message = __('Mandatory fields are not filled!');
      Session::addMessageAfterRedirect($message, false, ERROR);
      Html::back();
   }

   if ($newid = $comment->add($_POST)) {
      Session::addMessageAfterRedirect(
         "<a href='#eventcomment$newid'>" . __('Your comment has been added') . "</a>",
         false,
         INFO
      );
   }
   Html::back();
}

if (isset($_POST["edit"])) {
   if (!isset($_POST['plugin_eventsmanager_events_id']) || !isset($_POST['id']) || !isset($_POST['comment'])) {
      $message = __('Mandatory fields are not filled!');
      Session::addMessageAfterRedirect($message, false, ERROR);
      Html::back();
   }

   // Prevent IDOR: only the comment's author may edit it, and it must belong to
   // the event the caller is authorized on.
   if (!$comment->getFromDB((int) $_POST['id'])
       || (int) $comment->fields['users_id'] !== Session::getLoginUserID()
       || (int) $comment->fields['plugin_eventsmanager_events_id'] !== $events_id) {
       throw new AccessDeniedHttpException();
   }
   $data = array_merge($comment->fields, $_POST);
   if ($comment->update($data)) {
      //\Glpi\Event::log($_POST["knowbaseitems_id"], "knowbaseitem_comment", 4, "tracking",
      //            sprintf(__('%s edit a comment on knowledge base'), $_SESSION["glpiname"]));
      Session::addMessageAfterRedirect(
         "<a href='#eventcomment{$comment->getID()}'>" . __('Your comment has been edited') . "</a>",
         false,
         INFO
      );
   }
   Html::back();
}

if (isset($_POST["purge"])) {
   if (!isset($_POST['id'])) {
      $message = __('Mandatory fields are not filled!');
      Session::addMessageAfterRedirect($message, false, ERROR);
      Html::back();
   }

   // Prevent IDOR: only the comment's author may delete it, and it must belong
   // to the event the caller is authorized on.
   if (!$comment->getFromDB((int) $_POST['id'])
       || (int) $comment->fields['users_id'] !== Session::getLoginUserID()
       || (int) $comment->fields['plugin_eventsmanager_events_id'] !== $events_id) {
       throw new AccessDeniedHttpException();
   }
   if ($comment->delete(['id' => (int) $_POST['id']], true)) {
      Session::addMessageAfterRedirect(
         __('Your comment has been deleted'),
         false,
         INFO
      );
   }
   Html::back();
}

throw new BadRequestHttpException();

