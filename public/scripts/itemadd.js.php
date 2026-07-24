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

$root_eventsmanager_doc = PLUGIN_EVENTMANAGER_WEBDIR;

$JS = <<<JAVASCRIPT
// Reload the associated-item add form after an add/delete action.
// The runtime "rand" and "params" are read from the #itemAddForm{rand} container
// so the same handler works across the AJAX-reloaded fragments (event delegation).
function pluginEventsmanagerItemAction(container, rand, action, itemtype, items_id) {
    $.ajax({
        url: '$root_eventsmanager_doc/ajax/itemevent.php',
        dataType: 'html',
        data: {
            'action'  : action,
            'rand'    : rand,
            'params'  : container.data('params'),
            'my_items': $('#dropdown_my_items' + rand).val(),
            'itemtype': (itemtype === undefined || itemtype === null)
                ? $('#dropdown_itemtype' + rand).val() : itemtype,
            'items_id': (items_id === undefined || items_id === null)
                ? $('#dropdown_add_items_id' + rand).val() : items_id
        },
        success: function(response) {
            container.html(response);
        }
    });
}

$(document).on('click', '.event-item-action', function(e) {
    e.preventDefault();
    var _btn       = $(this);
    var _container = _btn.closest('[id^="itemAddForm"]');
    pluginEventsmanagerItemAction(
        _container,
        _container.data('rand'),
        _btn.data('item-action'),
        _btn.data('itemtype'),
        _btn.data('items_id')
    );
});
JAVASCRIPT;
echo $JS;
