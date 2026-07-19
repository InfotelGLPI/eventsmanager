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
use GlpiPlugin\Eventsmanager\Event_Comment;
use GlpiPlugin\Eventsmanager\Event;

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!isset($_POST['plugin_eventsmanager_events_id'])) {
   throw new \RuntimeException('Required argument missing!');
}

$event_id = (int) $_POST['plugin_eventsmanager_events_id'];
$lang = null;

// Enforce plugin right + entity access on the parent event before returning any
// comment form (prevents cross-entity disclosure of comment content).
$event = new Event();
if (!$event->can($event_id, UPDATE)) {
    throw new AccessDeniedHttpException();
}

$edit = false;
if (isset($_POST['edit'])) {
   $edit = (int) $_POST['edit'];
   // Only the author may load the edit form of a comment, and it must belong to
   // this event (defends against IDOR read of arbitrary comments).
   $comment = new Event_Comment();
   if (!$comment->getFromDB($edit)
       || (int) $comment->fields['users_id'] !== Session::getLoginUserID()
       || (int) $comment->fields['plugin_eventsmanager_events_id'] !== $event_id) {
       throw new AccessDeniedHttpException();
   }
}

$answer = false;
if (isset($_POST['answer'])) {
   $answer = $_POST['answer'];
}

echo Event_Comment::getCommentForm($event_id, $edit, $answer);
