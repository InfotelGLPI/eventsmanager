/**
 * -------------------------------------------------------------------------
 * eventsmanager plugin for GLPI
 * Copyright (C) 2017-2026 by the eventsmanager Development Team.
 *
 * https://github.com/InfotelGLPI/eventsmanager
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of eventsmanager.
 *
 * eventsmanager is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * eventsmanager is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with eventsmanager. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

// Plugin web root, mirroring PLUGIN_EVENTMANAGER_WEBDIR from setup.php. GLPI
// exposes both variables in the page <head> (config_js) before any plugin
// script is loaded, so no server-side interpolation is needed here.
var root_eventsmanger_doc = ((window.CFG_GLPI && CFG_GLPI.root_doc) || '')
   + ((window.GLPI_PLUGINS_PATH && GLPI_PLUGINS_PATH.eventsmanager) || '/plugins/eventsmanager');

function addUserEvent(event) {
    var conf = confirm(__('The current user will be added', 'eventsmanager'));
    if (conf) {
        $.ajax({
            url: root_eventsmanger_doc + '/ajax/adduser.php',
            type: "POST",
            data: {"id": event},
            success: function () {
                window.location.reload();
            }
        });
    }
}


function createTicketEvent(event) {
    var conf = confirm(__('A ticket will be created from the event', 'eventsmanager'));
    if (conf) {
        $.ajax({
            url: root_eventsmanger_doc + '/ajax/createticket.php',
            type: "POST",
            data: {"id": event},
            success: function () {
                window.location.reload();
            }
        });
    }
}

function closeEvent(event) {
    var conf = confirm(__('The event will be closed', 'eventsmanager'));
    if (conf) {
        $.ajax({
            url: root_eventsmanger_doc + '/ajax/closeevent.php',
            type: "POST",
            data: {"id": event},
            success: function () {
                window.location.reload();
            }
        });
    }
}

// Delegated handler for the event action icons (assign / create ticket / close).
$(document).on('click', '.event-action', function() {
    var _id = $(this).data('event-id');
    switch ($(this).data('action')) {
        case 'assign':
            addUserEvent(_id);
            break;
        case 'ticket':
            createTicketEvent(_id);
            break;
        case 'close':
            closeEvent(_id);
            break;
    }
});
