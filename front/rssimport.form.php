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

use GlpiPlugin\Eventsmanager\Rssimport;

// Import configuration drives automated event creation; require the plugin UPDATE right.
Session::checkRight('plugin_eventsmanager', UPDATE);

if (isset($_POST['update'])) {
    $rss   = new Rssimport();
    $input = [
        'use_with_plugin'    => (int) ($_POST['use_with_plugin'] ?? 0),
        'default_impact'     => (int) ($_POST['default_impact'] ?? 0),
        'default_priority'   => (int) ($_POST['default_priority'] ?? 0),
        'default_eventtype'  => (int) ($_POST['default_eventtype'] ?? 0),
        'entities_id_import' => (int) ($_POST['entities_id_import'] ?? 0),
    ];
    // The import entity is where the cron files the events: it must be one the caller reaches
    if (!Session::haveAccessToEntity($input['entities_id_import'])) {
        throw new Glpi\Exception\Http\AccessDeniedHttpException();
    }
    if ((int) ($_POST['id'] ?? 0) > 0) {
        // check() replays UPDATE on the RSS feed of the stored row
        $rss->check((int) $_POST['id'], UPDATE);
        $rss->update(['id' => $rss->getID()] + $input);
    } else {
        // First save: the tab only showed the defaults, the row is created now
        $input['rssfeeds_id']      = (int) ($_POST['rssfeeds_id'] ?? 0);
        $input['last_rssfeed_url'] = '';
        $rss->check(-1, CREATE, $input);
        if (!$rss->getFromDBByCrit(['rssfeeds_id' => $input['rssfeeds_id']])) {
            $rss->add($input);
        }
    }
    Html::back();
}
