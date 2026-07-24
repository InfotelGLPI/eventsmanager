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

namespace GlpiPlugin\Eventsmanager;

use Ajax;
use AllowDynamicProperties;
use CommonGLPI;
use CommonITILObject;
use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Mydashboard\Datatable;
use GlpiPlugin\Mydashboard\Menu;
use GlpiPlugin\Mydashboard\Widget;
use Group_User;
use Html;
use Plugin;

#[AllowDynamicProperties]
class Dashboard extends CommonGLPI
{
    public $widgets = [];
    private $options;
    private $form;

    /**
     * Dashboard constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->options = $options;
        $this->interfaces = ["central"];
    }

    public function init()
    {
    }


    /**
     * @return array[][]
     */
    public function getWidgetsForItem()
    {
        $widgets = [
            Menu::$TOOLS => [
                $this->getType() . "1" => [
                    "title" => _n('Event manager', 'Events manager', 2, 'eventsmanager'),
                    "type" => Widget::$TABLE,
                    "comment" => ""
                ],
            ],
        ];

        return $widgets;
    }

    /**
     * @param $widgetId
     *
     * @return Datatable
     */
    public function getWidgetContentForItem($widgetId)
    {
        global $DB;

        if (empty($this->form)) {
            $this->init();
        }
        switch ($widgetId) {
            case $this->getType() . "1":
                if (Plugin::isPluginActive("eventsmanager")) {
                    $widget = new Datatable();
                    $headers = [
                        __('Name'),
                        __('Status'),
                        __('Priority'),
                        __('Creation date'),
                        __('Event type', 'eventsmanager'),
                        __('Actions', 'eventsmanager')
                    ];

                    $criteria = [
                        'SELECT' => ['*'],
                        'FROM' => 'glpi_plugin_eventsmanager_events',
                        'WHERE' => [
                            'glpi_plugin_eventsmanager_events.is_deleted' => 0,
                            'NOT'       => ['status' => Event::CLOSED_STATE]
                        ],
                        'ORDERBY' => 'date_creation DESC',
                        'LIMIT' => 50
                    ];
                    $criteria['WHERE'] = $criteria['WHERE'] + getEntitiesRestrictCriteria(
                            'glpi_plugin_eventsmanager_events'
                        );

                    $iterator = $DB->request($criteria);

                    $events = [];
                    if (count($iterator) > 0) {
                        foreach ($iterator as $data) {
                            //$groups = Group_User::getGroupUsers($data['groups_id']);
                            $groupusers = Group_User::getGroupUsers($data['groups_id']);
                            $groups = [];
                            foreach ($groupusers as $groupuser) {
                                $groups[] = $groupuser["id"];
                            }
                            $rand = mt_rand();
                            $url = PLUGIN_EVENTMANAGER_WEBDIR . "/front/event.form.php" . "?id=" . $data['id'];
                            $renderer = TemplateRenderer::getInstance();

                            $events[$data['id']][0] = $renderer->render('@eventsmanager/dashboard_cell.html.twig', [
                                'kind'      => 'name',
                                'anchor_id' => 'event' . $data["id"] . $rand,
                                'url'       => $url,
                                'name'      => $data['name'],
                                'tooltip'   => Html::showToolTip($data['comment'], [
                                    'applyto' => 'event' . $data["id"] . $rand,
                                    'display' => false,
                                ]),
                            ]);

                            $events[$data['id']][1] = Event::getStatusName($data['status']);
                            $events[$data['id']][2] = $renderer->render('@eventsmanager/dashboard_cell.html.twig', [
                                'kind'          => 'priority',
                                'bgcolor'       => $_SESSION["glpipriority_" . $data['priority']],
                                'priority_name' => CommonITILObject::getPriorityName($data['priority']),
                            ]);

                            $date_creation = $data['date_creation'];
                            $events[$data['id']][3] = $renderer->render('@eventsmanager/dashboard_cell.html.twig', [
                                'kind'         => 'date',
                                'date_display' => Html::convDateTime($data['date_creation']),
                                'is_deleted'   => ($date_creation <= date('Y-m-d') && !empty($date_creation)),
                            ]);

                            $events[$data['id']][4] = $renderer->render('@eventsmanager/dashboard_cell.html.twig', [
                                'kind'       => 'eventtype',
                                'type_color' => Event::getTypeColor($data['eventtype']),
                                'type_name'  => Event::getEventTypeName($data['eventtype']),
                            ]);

                            $events[$data['id']][5] = $renderer->render('@eventsmanager/dashboard_cell.html.twig', [
                                'kind'    => 'actions',
                                'actions' => Event::getActionAff($data['id'], $data['status']),
                            ]);
                        }
                    }

                    $widget->setTabDatas($events);
                    $widget->setTabNames($headers);
                    $widget->setOption("bSort", false);
                    $widget->toggleWidgetRefresh();

                    $iframe = Ajax::createIframeModalWindow(
                        'event',
                        PLUGIN_EVENTMANAGER_WEBDIR . "/front/event.form.php",
                        [
                            'title' => __('Add event', 'eventsmanager'),
                            'reloadonclose' => false,
                            'width' => 1180,
                            'display' => false,
                            'height' => 600,
                        ]
                    );
                    $link = TemplateRenderer::getInstance()->render('@eventsmanager/dashboard_addbutton.html.twig', [
                        'iframe' => $iframe,
                    ]);
                    $widget->appendWidgetHtmlContent($link);
                    $widget->setWidgetTitle(_n('Event manager', 'Events manager', 2, 'eventsmanager'));

                    return $widget;
                } else {
                    $widget = new Datatable();
                    $widget->setWidgetTitle(_n('Event manager', 'Events manager', 2, 'eventsmanager'));
                    return $widget;
                }
        }
        return false;
    }
}
