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

use GlpiPlugin\Eventsmanager\Mailimport;

Session::checkLoginUser();
// Import configuration drives automated event creation; require the plugin UPDATE right.
Session::checkRight('plugin_eventsmanager', UPDATE);

if (isset($_POST['update'])) {
    if (isset($_POST['id'])) {
        $mail = new Mailimport();
        $mail->update($_POST);
        Html::back();
    }
}
