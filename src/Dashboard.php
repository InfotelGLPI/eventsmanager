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

namespace GlpiPlugin\Eventsmanager;

use CommonGLPI;
use CommonITILObject;
use DbUtils;
use GlpiPlugin\Mydashboard\Datatable;
use GlpiPlugin\Mydashboard\Menu;
use GlpiPlugin\Mydashboard\Widget;
use Group_User;
use Html;
use Plugin;

class Dashboard extends CommonGLPI
{

    public $widgets = [];
    private $options;
    private $datas, $form;

   /**
    * Dashboard constructor.
    *
    * @param array $options
    */
    function __construct($options = [])
    {
        $this->options    = $options;
        $this->interfaces = ["central"];
    }

    function init()
    {
    }


    /**
     * @return \array[][]
     */
    public function getWidgetsForItem()
    {
        $widgets = [
            Menu::$TOOLS => [
                $this->getType() . "1" => ["title"   => _n('Event manager', 'Events manager', 2, 'eventsmanager'),
                                           "type"    => Widget::$TABLE,
                                           "comment" => ""],
            ],
        ];

        return $widgets;
    }

   /**
    * @param $widgetId
    *
    * @return Datatable
    */
    function getWidgetContentForItem($widgetId)
    {
        global $CFG_GLPI, $DB;

        $dbu = new DbUtils();
        if (empty($this->form)) {
            $this->init();
        }
        switch ($widgetId) {
            case $this->getType() . "1":
                if (Plugin::isPluginActive("eventsmanager")) {
                    $widget  = new Datatable();
                    $headers = [__('Name'),
                           __('Status'),
                           __('Priority'),
                           __('Creation date'),
                           __('Event type', 'eventsmanager'),
                           __('Actions', 'eventsmanager')];
                    $query   = "SELECT *
                            FROM `glpi_plugin_eventsmanager_events`
                            WHERE NOT `glpi_plugin_eventsmanager_events`.`is_deleted`";
                    $query   .= $dbu->getEntitiesRestrictRequest('AND', 'glpi_plugin_eventsmanager_events');
                    $query   .= "ORDER BY `glpi_plugin_eventsmanager_events`.`date_creation` DESC LIMIT 50 ";

                    $events = [];
                    if ($result = $DB->doQuery($query)) {
                        if ($DB->numrows($result)) {
                            while ($data = $DB->fetchArray($result)) {
                                //$groups = Group_User::getGroupUsers($data['groups_id']);
                                $groupusers = Group_User::getGroupUsers($data['groups_id']);
                                $groups     = [];
                                foreach ($groupusers as $groupuser) {
                                    $groups[] = $groupuser["id"];
                                }
                                if ($data["status"] < Event::CLOSED_STATE) {
                                    $ID                     = $data['id'];
                                    $rand                   = mt_rand();
                                    $url                    = PLUGIN_EVENTMANAGER_WEBDIR . "/front/event.form.php" . "?id=" . $data['id'];
                                    $events[$data['id']][0] = "<a id='event" . $data["id"] . $rand . "' target='_blank' href='$url'>" . $data['name'] . "</a>";

                                    $events[$data['id']][0] .= Html::showToolTip($data['comment'], ['applyto' => 'event' . $data["id"] . $rand,
                                                                                         'display' => false]);

                                    $bgcolor                = $_SESSION["glpipriority_" . $data['priority']];
                                    $events[$data['id']][1] = Event::getStatusName($data['status']);
                                    $events[$data['id']][2] = "<div class='center' style='background-color:$bgcolor;'>" . CommonITILObject::getPriorityName($data['priority']) . "</div>";
                                    $date_creation          = $data['date_creation'];
                                    $display                = Html::convDateTime($data['date_creation']);
                                    if ($date_creation <= date('Y-m-d') && !empty($date_creation)) {
                                         $display = "<div class='deleted'>" . Html::convDateTime($data['date_creation']) . "</div>";
                                    }
                                    $events[$data['id']][3] = $display;
                                    $events[$data['id']][4] = "<div class='center' style='" . Event::getTypeColor($data['eventtype']) . "'>" . Event::getEventTypeName($data['eventtype']) . "</div>";
                                    if ($data['status'] < Event::CLOSED_STATE) {
                                        $events[$data['id']][5] = '<div style="min-width:100px;">' . Event::getActionAff($data['id'], $data['status']) . '</div>';
                                    } else {
                                        $events[$data['id']][5] = __('No action avalable', 'eventsmanager');
                                    }
                                }
                            }
                        }
                    }

                    $widget->setTabDatas($events);
                    $widget->setTabNames($headers);
                    $widget->setOption("bSort", false);
                    $widget->toggleWidgetRefresh();

                    $link = "<div align='right'><a href='#' class='submit btn btn-primary' data-bs-toggle='modal' data-bs-target='#event' title='" . __('Add event', 'eventsmanager') . "' >";
                    $link .= __('Add event', 'eventsmanager');
                    $link .= "</a></div>";

                    $link .= Ajax::createIframeModalWindow('event', PLUGIN_EVENTMANAGER_WEBDIR . "/front/event.form.php", ['title'         => __('Add event', 'eventsmanager'),
                                                                                                                      'reloadonclose' => false,
                                                                                                                      'width'         => 1180,
                                                                                                                      'display'       => false,
                                                                                                                      'height'        => 600
                    ]);
                     $widget->appendWidgetHtmlContent($link);
                     $widget->setWidgetTitle(_n('Event manager', 'Events manager', 2, 'eventsmanager'));

                     return $widget;
                } else {
                    $widget = new Datatable();
                    $widget->setWidgetTitle(_n('Event manager', 'Events manager', 2, 'eventsmanager'));
                    return $widget;
                }
                break;
        }
    }
}
