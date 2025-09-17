<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 eventsmanager plugin for GLPI
 Copyright (C) 2017-2022 by the eventsmanager Development Team.

 https://github.com/InfotelGLPI/eventsmanager
 -------------------------------------------------------------------------

 LICENSE

 This file is part of eventsmanager.

 eventsmanager is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 eventsmanager is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with eventsmanager. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

use Glpi\Plugin\Hooks;
use GlpiPlugin\Eventsmanager\Dashboard;
use GlpiPlugin\Eventsmanager\Event;
use GlpiPlugin\Eventsmanager\Mailimport;
use GlpiPlugin\Eventsmanager\Profile;
use GlpiPlugin\Eventsmanager\Rssimport;
use GlpiPlugin\Eventsmanager\Ticket;
use GlpiPlugin\Mydashboard\Menu;
use GlpiPlugin\Mydashboard\Alert;

global $CFG_GLPI;

define('PLUGIN_EVENTMANAGER_VERSION', '4.0.0');

if (!defined("PLUGIN_EVENTMANAGER_DIR")) {
    define("PLUGIN_EVENTMANAGER_DIR", Plugin::getPhpDir("eventsmanager"));
    $root = $CFG_GLPI['root_doc'] . '/plugins/eventsmanager';
    define("PLUGIN_EVENTMANAGER_WEBDIR", $root);
}

// Init the hooks of the plugins -Needed
function plugin_init_eventsmanager()
{
    global $PLUGIN_HOOKS, $CFG_GLPI;

    $PLUGIN_HOOKS['csrf_compliant']['eventsmanager'] = true;
    $PLUGIN_HOOKS['change_profile']['eventsmanager'] = [Profile::class, 'initProfile'];

    $PLUGIN_HOOKS['use_rules']['eventsmanager'] = ['RuleMailCollector'];

    Plugin::registerClass(Ticket::class, ['addtabon' => ['Ticket']]);
    Plugin::registerClass(Rssimport::class, ['addtabon' => ['RSSFeed']]);
    Plugin::registerClass(Mailimport::class, ['addtabon' => ['MailCollector']]);

    if (Session::getLoginUserID()) {
        Plugin::registerClass(Event::class, [
         'assignable_types' => true,
        ]);

        Plugin::registerClass(
            Profile::class,
            ['addtabon' => 'Profile']
        );

        if (Session::haveRight("plugin_eventsmanager", UPDATE)) {
            $PLUGIN_HOOKS['use_massive_action']['eventsmanager'] = 1;
            $PLUGIN_HOOKS['config_page']['eventsmanager']        = 'front/config.form.php';
        }

        if (Session::haveRight("plugin_eventsmanager", READ)) {
            $PLUGIN_HOOKS['menu_toadd']['eventsmanager'] = ['helpdesk' => Event::class];
        }

        if (class_exists(Menu::class)) {
            $PLUGIN_HOOKS['mydashboard']['eventsmanager'] = [Dashboard::class];
        }

        if (Session::haveRight("plugin_eventsmanager", CREATE)) {
            $PLUGIN_HOOKS['use_massive_action']['eventsmanager'] = 1;
        }

        $PLUGIN_HOOKS['item_purge']['eventsmanager']['Ticket'] = [Ticket::class, 'cleanForTicket'];

        if (isset($_SESSION["glpiactiveprofile"])
             && $_SESSION["glpiactiveprofile"]["interface"] != "helpdesk") {
            $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['eventsmanager'][] = 'scripts/jsForAction.js.php';
        }
    }

    if (Plugin::isPluginActive('mydashboard')) {
        Plugin::registerClass(
            Alert::class,
            ['addtabon' => Event::class]
        );
    }
}

// Get the name and the version of the plugin - Needed
/**
 * @return array
 */
function plugin_version_eventsmanager()
{

    return [
      'name'           => _n('Event manager', 'Events manager', 2, 'eventsmanager'),
      'version'        => PLUGIN_EVENTMANAGER_VERSION,
      'license'        => 'GPLv2+',
      'author'         => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
      'homepage'       => 'https://github.com/InfotelGLPI/eventsmanager',
      'requirements' => [
         'glpi' => [
            'min' => '11.0',
            'max' => '12.0',
            'dev' => false
         ]
      ]
    ];
}
