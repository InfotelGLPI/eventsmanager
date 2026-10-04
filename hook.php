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

use Glpi\DBAL\QuerySubQuery;
use GlpiPlugin\Eventsmanager\Event;
use GlpiPlugin\Eventsmanager\Event_Comment;
use GlpiPlugin\Eventsmanager\Event_Item;
use GlpiPlugin\Eventsmanager\Mailimport;
use GlpiPlugin\Eventsmanager\Origin;
use GlpiPlugin\Eventsmanager\Profile;
use GlpiPlugin\Eventsmanager\Rssimport;
use GlpiPlugin\Eventsmanager\Ticket;

/**
 * @return bool
 */
function plugin_eventsmanager_install()
{
    global $DB;

    $update = true;

    if (!$DB->tableExists("glpi_plugin_eventsmanager_events")) {
        $update = false;
        $DB->runFile(PLUGIN_EVENTMANAGER_DIR . "/sql/empty-4.0.0.sql");
    }

    if ($update) {
        $DB->runFile(PLUGIN_EVENTMANAGER_DIR . "/sql/update-2.2.0.sql");
    }

    if ($update) {
        $DB->runFile(PLUGIN_EVENTMANAGER_DIR . "/sql/update-4.0.0.sql");
    }

    //DisplayPreferences Migration
    $classes = ['PluginEventsmanagerEvent' => Event::class];

    foreach ($classes as $old => $new) {
        $displayusers = $DB->request([
            'SELECT' => [
                'users_id',
            ],
            'DISTINCT' => true,
            'FROM' => 'glpi_displaypreferences',
            'WHERE' => [
                'itemtype' => $old,
            ],
        ]);

        if (count($displayusers) > 0) {
            foreach ($displayusers as $displayuser) {
                $iterator = $DB->request([
                    'SELECT' => [
                        'num',
                        'id',
                    ],
                    'FROM' => 'glpi_displaypreferences',
                    'WHERE' => [
                        'itemtype' => $old,
                        'users_id' => $displayuser['users_id'],
                        'interface' => 'central',
                    ],
                ]);

                if (count($iterator) > 0) {
                    foreach ($iterator as $data) {
                        $iterator2 = $DB->request([
                            'SELECT' => [
                                'id',
                            ],
                            'FROM' => 'glpi_displaypreferences',
                            'WHERE' => [
                                'itemtype' => $new,
                                'users_id' => $displayuser['users_id'],
                                'num' => $data['num'],
                                'interface' => 'central',
                            ],
                        ]);
                        if (count($iterator2) > 0) {
                            foreach ($iterator2 as $dataid) {
                                $DB->delete('glpi_displaypreferences', ['id' => $dataid['id']]);
                            }
                        } else {
                            $DB->update('glpi_displaypreferences', ['itemtype' => $new], ['id' => $data['id']]);
                        }
                    }
                }
            }
        }
    }

    Profile::initProfile();
    Profile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
    CronTask::Register(Rssimport::class, 'RssImport', DAY_TIMESTAMP);

    return true;
}

/**
 * @return bool
 */
function plugin_eventsmanager_uninstall()
{
    global $DB;

    $tables = [
        "glpi_plugin_eventsmanager_events",
        "glpi_plugin_eventsmanager_rssimports",
        "glpi_plugin_eventsmanager_tickets",
        "glpi_plugin_eventsmanager_origins",
        "glpi_plugin_eventsmanager_events_items",
        "glpi_plugin_eventsmanager_configs",
        "glpi_plugin_eventsmanager_events_comments",
        "glpi_plugin_eventsmanager_mailimports"];

    $itemtypes = [
        \Alert::class,
        \DisplayPreference::class,
        \Document_Item::class,
        \ImpactItem::class,
        \Item_Ticket::class,
        \Link_Itemtype::class,
        \Notepad::class,
        \SavedSearch::class,
        \DropdownTranslation::class,
        \NotificationTemplate::class,
        \Notification::class,
        \Log::class,
    ];
    // Every class of the plugin may own display preferences, logs, saved searches...
    $plugin_itemtypes = [
        Event::class,
        Event_Comment::class,
        Event_Item::class,
        Mailimport::class,
        Origin::class,
        Rssimport::class,
        Ticket::class,
    ];
    foreach ($itemtypes as $itemtype) {
        $item = new $itemtype();
        $item->deleteByCriteria(['itemtype' => $plugin_itemtypes]);
    }

    // Mail collector rule actions declared by plugin_eventsmanager_getRuleActions():
    // left behind, they would point to a missing plugin and come back on reinstall
    $DB->delete('glpi_ruleactions', [
        'field'    => 'eventsmanager',
        'rules_id' => new QuerySubQuery([
            'SELECT' => 'id',
            'FROM'   => 'glpi_rules',
            'WHERE'  => ['sub_type' => RuleMailCollector::class],
        ]),
    ]);

    // The RssImport task registered on install would otherwise stay listed in the automatic
    // actions, pointing to a class that no longer exists. Deleted by its exact itemtype:
    // CronTask::unregister()'s LIKE pattern does not match the backslashes of a namespaced
    // itemtype (checked against the local base), so it removed nothing.
    $DB->delete('glpi_crontasks', ['itemtype' => Rssimport::class]);

    //Delete rights associated with the plugin
    $profileRight = new ProfileRight();
    foreach (Profile::getAllRights() as $right) {
        $profileRight->deleteByCriteria(['name' => $right['field']]);
    }

    foreach ($tables as $table) {
        $DB->dropTable($table, true);
    }

    Event::removeRightsFromSession();

    Profile::removeRightsFromSession();

    return true;
}

// Define dropdown relations
/**
 * @return array
 */
function plugin_eventsmanager_getDatabaseRelations()
{

    if (Plugin::isPluginActive("eventsmanager")) {
        return [
            //            "glpi_users"          => ["glpi_plugin_eventsmanager_events" => "users_id",
            //                                                  "glpi_plugin_eventsmanager_events" => "users_assigned",
            //                                                  "glpi_plugin_eventsmanager_events" => "users_close"],
            //                   "glpi_groups"         => ["glpi_plugin_eventsmanager_events" => "groups_id",
            //                                                  "glpi_plugin_eventsmanager_events" => "groups_assigned"],
            "glpi_entities"       => ["glpi_plugin_eventsmanager_events"     => "entities_id",
                "glpi_plugin_eventsmanager_rssimports" => "entities_id_import"],
            "glpi_reminders"      => ["glpi_plugin_eventsmanager_events" => "reminders_id"],
            "glpi_requesttypes"   => ["glpi_plugin_eventsmanager_origins" => "requesttypes_id"],
            "glpi_tickets"        => ["glpi_plugin_eventsmanager_tickets" => "tickets_id"],
            "glpi_rssfeeds"       => ["glpi_plugin_eventsmanager_rssimports" => "rssfeeds_id"],
            "glpi_mailcollectors" => ["glpi_plugin_eventsmanager_mailimports" => "mailcollectors_id"]];
    } else {
        return [];
    }
}

// Define Dropdown tables to be manage in GLPI :
/**
 * @return array
 */
function plugin_eventsmanager_getDropdown()
{

    if (Plugin::isPluginActive("eventsmanager")) {
        return [Origin::class => Origin::getTypeName(2)];
    } else {
        return [];
    }
}

function plugin_eventsmanager_getAddSearchOptions($itemtype)
{

    $sopt = [];

    if ($itemtype == 'RSSFeed') {
        if (Session::haveRight("plugin_eventsmanager", READ)) {
            $sopt = Rssimport::addSearchOptions($sopt);
        }
    }
    return $sopt;
}

/**
 * @param $type
 * @param $ID
 * @param $data
 * @param $num
 *
 * @return string
 */
function plugin_eventsmanager_displayConfigItem($type, $ID, $data, $num)
{

    $searchopt = Search::getOptions($type);
    $table     = $searchopt[$ID]["table"];
    $field     = $searchopt[$ID]["field"];

    switch ($table . '.' . $field) {
        case "glpi_plugin_eventsmanager_events.priority":
            return " style=\"background-color:" . $_SESSION["glpipriority_" . $data[$num][0]['name']] . ";\" ";
            break;
        case "glpi_plugin_eventsmanager_events.eventtype":
            return ' style="' . Event::getTypeColor($data[$num][0]['name']) . ';"';
            break;
        case "glpi_plugin_eventsmanager_events.action":
            return ' style="min-width:100px;"';
            break;
    }
    return "";
}

/**
 * @param $options
 *
 * @return array
 */
function plugin_eventsmanager_getRuleActions($options)
{
    $event = new Event();
    return $event->getActions();
}

/**
 * @param $options
 *
 * @return mixed
 */
function plugin_eventsmanager_getRuleCriterias($options)
{
    $event = new Event();
    return $event->getCriterias();
}

/**
 * @param $options
 *
 * @return the
 */
function plugin_eventsmanager_executeActions($options)
{
    $event = new Event();
    return $event->executeActions($options['action'], $options['output'], $options['params']);
}
