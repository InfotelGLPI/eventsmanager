<?php

include("../../../../inc/includes.php");

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
