<?php

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

use GlpiPlugin\Eventsmanager\Mailimport;

// Import configuration drives automated event creation; require the plugin UPDATE right.
Session::checkRight('plugin_eventsmanager', UPDATE);

if (isset($_POST['update'])) {
    $mail  = new Mailimport();
    $input = [
        'default_impact'    => (int) ($_POST['default_impact'] ?? 0),
        'default_priority'  => (int) ($_POST['default_priority'] ?? 0),
        'default_eventtype' => (int) ($_POST['default_eventtype'] ?? 0),
    ];
    if ((int) ($_POST['id'] ?? 0) > 0) {
        // check() replays UPDATE on the mail collector of the stored row
        $mail->check((int) $_POST['id'], UPDATE);
        $mail->update(['id' => $mail->getID()] + $input);
    } else {
        // First save: the tab only showed the defaults, the row is created now
        $input['mailcollectors_id'] = (int) ($_POST['mailcollectors_id'] ?? 0);
        $mail->check(-1, CREATE, $input);
        if (!$mail->getFromDBByCrit(['mailcollectors_id' => $input['mailcollectors_id']])) {
            $mail->add($input);
        }
    }
    Html::back();
}
