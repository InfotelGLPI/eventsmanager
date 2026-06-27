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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly.");
}

//change mimetype
header("Content-type: application/javascript");

$confirm_user_event    = __('The current user will be added', 'eventsmanager');
$confirm_ticket_event   = __('A ticket will be created from the event', 'eventsmanager');
$confirm_close_event = __('The event will be closed', 'eventsmanager');

$root_eventsmanger_doc = PLUGIN_EVENTMANAGER_WEBDIR;
$JS = <<<JAVASCRIPT
function addUserEvent(event) {
    var conf = confirm('$confirm_user_event');
    if (conf) {
        $.ajax({
            url: '$root_eventsmanger_doc/ajax/adduser.php',
            type: "POST",
            data: {"id": event},
            success: function () {
                window.location.reload();
            }
        });
    }
}


function createTicketEvent(event) {
    var conf = confirm('$confirm_ticket_event');
    if (conf) {
        $.ajax({
            url: '$root_eventsmanger_doc/ajax/createticket.php',
            type: "POST",
            data: {"id": event},
            success: function () {
                window.location.reload();
            }
        });
    }
}

function closeEvent(event) {
    var conf = confirm('$confirm_close_event');
    if (conf) {
        $.ajax({
            url: '$root_eventsmanger_doc/ajax/closeevent.php',
            type: "POST",
            data: {"id": event},
            success: function () {
                window.location.reload();
            }
        });
    }
}
JAVASCRIPT;
echo $JS;
