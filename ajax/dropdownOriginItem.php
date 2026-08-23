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

use GlpiPlugin\Eventsmanager\Origin;

if (strpos($_SERVER['PHP_SELF'], "dropdownOriginItem.php")) {
    header("Content-Type: text/html; charset=UTF-8");
    Html::header_nocache();
}

Session::checkRight('plugin_eventsmanager', READ);

$field = new Origin();
$id    = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $field->getFromDB($id);
} else {
    $field->getEmpty();
    // itemtype is an attacker-supplied numeric Origin constant: restrict it to the
    // known source types before storing it on the object, so only the closed switch
    // in Origin::dropdownItems() can ever act on it (defense in depth).
    $itemtype = (int) ($_POST['itemtype'] ?? 0);
    if (in_array($itemtype, [Origin::Collector, Origin::RSS, Origin::Api, Origin::Others], true)) {
        $field->fields['itemtype'] = $itemtype;
    }
}
Origin::selectItems($field);
