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

if (strpos($_SERVER['PHP_SELF'], "adduser.php")) {
    $AJAX_INCLUDE = 1;
    header("Content-Type: text/html; charset=UTF-8");
    Html::header_nocache();
}

Session::checkCentralAccess();

$user = $_SESSION['glpiID'];
$date = $_SESSION['glpi_currenttime'];

if (isset($_POST['id'])) {
    $id    = (int) $_POST['id'];
    $event = new Event();
    // can(UPDATE) enforces the plugin right AND entity access on the target event.
    if (!$event->can($id, UPDATE)) {
        throw new AccessDeniedHttpException();
    }
    $event->update(['id'             => $id,
        'users_assigned' => $user,
        'date_assign'    => $date,
        'status'          => Event::ASSIGNED_STATE]);
}
