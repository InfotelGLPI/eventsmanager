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

use CommonDBTM;
use CommonGLPI;
use CommonITILObject;
use DbUtils;
use Document_Item;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Glpi\RichText\RichText;
use Html;
use Item_Ticket;
use Session;
use Toolbox;

class Ticket extends CommonDBTM
{
    public static $rightname = 'plugin_eventsmanager';

    /**
     * Returns the type name with consideration of plural
     *
     * @param number $nb Number of item(s)
     *
     * @return string Itemtype name
     */
    public static function getTypeName($nb = 0)
    {
        return _n('Ticket', 'Tickets', $nb);
    }

    public static function getIcon()
    {
        return Event::getIcon();
    }
    /**
     * Return the name of the tab for item including forms like the config page
     *
     * @param CommonGLPI $item Instance of a CommonGLPI Item (The Config Item)
     * @param integer    $withtemplate
     *
     * @return String                   Name to be displayed
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $dbu = new DbUtils();
        if (Session::getCurrentInterface() == 'central' && Session::haveRight(self::$rightname, READ)) {
            switch ($item->getType()) {
                case Event::class:
                    $nb = 0;
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = $dbu->countElementsInTable(
                            'glpi_plugin_eventsmanager_tickets',
                            ["plugin_eventsmanager_events_id" => $item->getID()]
                        );
                    }
                    return self::createTabEntry(self::getTypeName($nb), $nb);
                    break;
                case "Ticket":
                    $nb = 0;
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = $dbu->countElementsInTable(
                            'glpi_plugin_eventsmanager_tickets',
                            ["tickets_id" => $item->getID()]
                        );
                    }
                    return self::createTabEntry(_n('Linked event', 'Linked events', $nb, 'eventsmanager'), $nb);
                    break;
            }
        }
        return '';
    }

    /**
     * @param CommonGLPI $item
     * @param int        $tabnum
     * @param int        $withtemplate
     *
     * @return bool
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $ticket = new self();

        switch ($item->getType()) {
            case Event::class:
                $ID = $item->getField('id');
                $ticket->showForEvent($ID);
                break;
            case "Ticket":
                $ID = $item->getField('id');
                $ticket->showForTicket($item);
                break;
        }
    }

    /**
     * @param $id
     */
    public static function addTicketFromEvent($id)
    {

        $evt          = new Event();
        $ticket       = new \Ticket();
        $item         = new Item_Ticket();
        $event_ticket = new Ticket();

        if ($evt->getFromDB($id)) {

            $users_id_recipient = $_SESSION['glpiID'];
            $date               = $_SESSION['glpi_currenttime'];
            $name               = $evt->fields['name'];
            $entities_id        = $evt->fields['entities_id'];
            $user_id            = $evt->fields['users_close'];
            $requesttype        = 0;
            $origin             = new Origin();
            if ($evt->fields['plugin_eventsmanager_origins_id'] > 0
                && $origin->getFromDB($evt->fields['plugin_eventsmanager_origins_id'])) {
                if ($origin->fields['requesttypes_id'] > 0) {
                    $requesttype = $origin->fields['requesttypes_id'];
                }
            }

            $tickets_id = $ticket->add(['name'               => $name,
                'entities_id'        => $entities_id,
                'date'               => $date,
                '_users_id_requester' => $users_id_recipient,
                'users_id_recipient' => $users_id_recipient,
                'requesttypes_id'    => $requesttype,
                'content'            => $evt->fields['comment'],
                'priority'           => $evt->fields['priority'],
                'impact'             => $evt->fields['impact'],
                'time_to_resolve'    => $evt->fields['time_to_resolve'],
                'type'               => \Ticket::INCIDENT_TYPE]);
            /*
             * Modification association document to ticket
             */
            if ($tickets_id > 0) {

                $doc_item = new Document_Item();
                $alldocs  = $doc_item->find(["items_id" => $id,
                    'itemtype' => $evt->getType()]);
                foreach ($alldocs as $key => $value) {

                    $input                 = [];
                    $input["documents_id"] = $value["documents_id"];
                    $input["itemtype"]     = $ticket->getType();
                    $input["entities_id"]  = $value["entities_id"];
                    $input["is_recursive"] = $value["is_recursive"];
                    $input["users_id"]     = $value["users_id"];
                    $input["items_id"]     = $tickets_id;
                    $doc_item->add($input);
                }
                /*
                 * End modification
                 */
                $event_item = new Event_Item();
                $items      = $event_item->getUsedItems($id);

                foreach ($items as $itemtype => $obj) {
                    foreach ($obj as $object => $items_id) {
                        $item->add(['itemtype'   => $itemtype,
                            'items_id'   => $items_id,
                            'tickets_id' => $tickets_id]);
                    }
                }

                $event_ticket->add(['plugin_eventsmanager_events_id' => $id,
                    'tickets_id'                     => $tickets_id]);

                $config = new Config();
                $config->getFromDB(1);

                if ($config->fields['use_automatic_close']) {
                    $evt->update(['id'          => $id,
                        'ticket'      => $tickets_id,
                        'users_close' => $user_id,
                        'status'      => Event::CLOSED_STATE]);
                }
            }
        }
    }

    /**
     * @param $ID
     */
    public static function cleanForTicket($item)
    {

        $temp = new self();
        $temp->deleteByCriteria(['tickets_id' => $item->getID()]);

    }

    /**
     * @param       $ID
     * @param array $options
     */
    public function showForTicket($ticket)
    {
        global $DB;

        $ID = $ticket->getField('id');
        if (!$ticket->can($ID, READ)) {
            return false;
        }

        $canedit = $ticket->canEdit($ID);
        $rand    = mt_rand();

        $iterator = $DB->request([
            'SELECT'    => [
                'glpi_plugin_eventsmanager_events.*',
                'glpi_plugin_eventsmanager_tickets.id AS LinkID',
            ],
            'DISTINCT'  => true,
            'FROM'      => 'glpi_plugin_eventsmanager_tickets',
            'LEFT JOIN' => [
                'glpi_plugin_eventsmanager_events' => [
                    'ON' => [
                        'glpi_plugin_eventsmanager_tickets' => 'plugin_eventsmanager_events_id',
                        'glpi_plugin_eventsmanager_events'  => 'id',
                    ],
                ],
            ],
            'WHERE'     => ['glpi_plugin_eventsmanager_tickets.tickets_id' => $ID],
            'ORDER'     => 'glpi_plugin_eventsmanager_events.date_creation ASC',
        ]);

        $tickets = [];
        $used    = [];
        foreach ($iterator as $data) {
            $tickets[$data['id']] = $data;
            $used[$data['id']]    = $data['id'];
        }
        $number  = count($tickets);

        // "Add an event" block (captured Event::dropdown)
        if ($canedit) {
            ob_start();
            Event::dropdown(['used'   => $used,
                'entity' => $ticket->getEntityID()]);
            $event_dropdown = ob_get_clean();

            TemplateRenderer::getInstance()->display('@eventsmanager/ticket_add_block.html.twig', [
                'add_form_action' => Toolbox::getItemTypeFormURL(__CLASS__),
                'ticket_id'       => $ID,
                'event_dropdown'  => $event_dropdown,
            ]);
        }

        // Build datatable entries
        $entries = [];
        foreach ($tickets as $data) {
            $event_url = Toolbox::getItemTypeFormURL(Event::class) . "?id=" . (int) $data['id'];
            $entry = [
                'itemtype' => self::class,
                'id'       => $data['LinkID'],
                'name'     => "<a href=\"" . htmlspecialchars($event_url) . "\">" . htmlspecialchars($data['name']) . "</a>",
                'date'     => Html::convDateTime($data['date_creation'], 1),
                'status'   => Event::getStatusName($data['status']),
                'priority' => "<div class='center' style='background-color:" . htmlspecialchars($_SESSION["glpipriority_" . $data['priority']]) . ";'>"
                    . CommonITILObject::getPriorityName($data['priority']) . "</div>",
                'eventtype' => ($data['eventtype'] > 0)
                    ? "<div class='center' style='" . Event::getTypeColor($data['eventtype']) . "'>" . Event::getEventTypeName($data['eventtype']) . "</div>"
                    : '',
            ];

            // Origin
            $origin_name    = Dropdown::getDropdownName('glpi_plugin_eventsmanager_origins', $data['plugin_eventsmanager_origins_id']);
            $itemtype_label = '';
            $item_name      = '';
            $origin         = new Origin();
            if ($origin->getFromDB($data["plugin_eventsmanager_origins_id"])) {
                $itemtype_label = Origin::getItemtypeOrigin($origin->fields['itemtype']);
                $item_name      = Origin::getItemOrigin('items_id', ["itemtype" => $origin->fields['itemtype'],
                    "items_id" => $origin->fields['items_id']]);
            }
            $entry['origin'] = TemplateRenderer::getInstance()->render('@eventsmanager/ticket_origin_cell.html.twig', [
                'origin_name'    => $origin_name,
                'itemtype_label' => $itemtype_label,
                'item_name'      => $item_name,
            ]);

            // Associated items (preserve legacy output)
            $event_item = new Event_Item();
            $items      = $event_item->getUsedItems($data['id']);
            $dbu_inner  = new DbUtils();
            $item_rows  = [];
            foreach ($items as $itemtype => $items_id) {
                if (!($item = $dbu_inner->getItemForItemtype($itemtype))) {
                    continue;
                }
                $typenames = '';
                foreach ($items_id as $item_id) {
                    $typenames .= $item::getTypeName();
                }
                $item->getFromDB($item_id);
                $item_rows[] = [
                    'typenames' => $typenames,
                    'link'      => $item->getLink(),
                ];
            }
            $entry['items'] = TemplateRenderer::getInstance()->render('@eventsmanager/associated_items_cell.html.twig', [
                'items' => $item_rows,
            ]);

            $entry['description'] = Html::resume_text(RichText::getTextFromHtml($data['comment'], false), 255);

            $entries[] = $entry;
        }

        TemplateRenderer::getInstance()->display('components/datatable.html.twig', [
            'is_tab'             => true,
            'nofilter'           => true,
            'nosort'             => true,
            'super_header'       => _n('Linked event', 'Linked events', $number, 'eventsmanager'),
            'columns'            => [
                'name'        => __('Name'),
                'date'        => __('Creation date'),
                'origin'      => Origin::getTypeName(1),
                'status'      => __('Status'),
                'priority'    => __('Priority'),
                'eventtype'   => __('Type'),
                'items'       => _n('Associated element', 'Associated elements', Session::getPluralNumber()),
                'description' => __('Description'),
            ],
            'formatters'         => [
                'name'        => 'raw_html',
                'origin'      => 'raw_html',
                'priority'    => 'raw_html',
                'eventtype'   => 'raw_html',
                'items'       => 'raw_html',
                'description' => 'raw_html',
            ],
            'entries'            => $entries,
            'total_number'       => $number,
            'filtered_number'    => $number,
            'showmassiveactions' => $canedit,
            'massiveactionparams' => [
                'num_displayed'    => $number,
                'specific_actions' => ['purge' => _x('button', 'Delete permanently')],
                'container'        => 'mass' . str_replace('\\', '', self::class) . $rand,
                'extraparams'      => ['tickets_id' => $ticket->getID()],
            ],
        ]);
    }

    /**
     * @param       $ID
     * @param array $options
     */
    public function showForEvent($ID, $options = [])
    {
        $event  = new Event();
        $ticket = new \Ticket();

        $event->getFromDB($ID);

        $show_link_form = ($event->fields['status'] < Event::CLOSED_STATE
            && $event->fields['status'] > 0);

        $ticket_dropdown = '';
        if ($show_link_form) {
            ob_start();
            \Ticket::dropdown(['name'        => "tickets_id",
                'entity'      => $event->getEntityID(),
                'entity_sons' => $event->isRecursive(),
                'displaywith' => ['id']]);
            $ticket_dropdown = ob_get_clean();
        }

        // "Link to tickets" form block
        if ($show_link_form) {
            TemplateRenderer::getInstance()->display('@eventsmanager/ticket_link_form.html.twig', [
                'create_form_action' => Toolbox::getItemTypeFormURL(Event::class),
                'event_id'           => (int) $ID,
                'ticket_dropdown'    => $ticket_dropdown,
                'link_event_id'      => $ID,
            ]);
        }

        $eventsmanager_ticket = new Ticket();
        $tickets              = $eventsmanager_ticket->find(['plugin_eventsmanager_events_id' => $event->fields['id']]);

        // Build datatable entries for linked tickets
        $entries = [];
        foreach ($tickets as $data) {
            if (!$ticket->getFromDB($data['tickets_id'])) {
                continue;
            }

            // Associated items (preserve legacy output)
            $item_ticket = new Item_Ticket();
            $items       = $item_ticket->getUsedItems($ticket->fields["id"]);
            $dbu_inner   = new DbUtils();
            $item_rows   = [];
            foreach ($items as $itemtype => $items_id) {
                if (!($item = $dbu_inner->getItemForItemtype($itemtype))) {
                    continue;
                }
                $typenames = '';
                foreach ($items_id as $item_id) {
                    $typenames .= $item::getTypeName();
                }
                $item->getFromDB($item_id);
                $item_rows[] = [
                    'typenames' => $typenames,
                    'link'      => $item->getLink(),
                ];
            }
            $items_html = TemplateRenderer::getInstance()->render('@eventsmanager/associated_items_cell.html.twig', [
                'items' => $item_rows,
            ]);

            $entries[] = [
                'name'     => $ticket->getLink(),
                'date'     => Html::convDateTime($ticket->fields["date"]),
                'status'   => \Ticket::getStatus($ticket->fields["status"]),
                'priority' => "<div class='center' style='background-color:" . htmlspecialchars($_SESSION["glpipriority_" . $ticket->fields['priority']]) . ";'>"
                    . CommonITILObject::getPriorityName($ticket->fields["priority"]) . "</div>",
                'items'    => $items_html,
            ];
        }

        TemplateRenderer::getInstance()->display('components/datatable.html.twig', [
            'is_tab'          => true,
            'nofilter'        => true,
            'nosort'          => true,
            'super_header'    => __('Linked tickets', 'eventsmanager'),
            'columns'         => [
                'name'     => __('Name'),
                'date'     => __('Date'),
                'status'   => __('Status'),
                'priority' => __('Priority'),
                'items'    => _n('Associated element', 'Associated elements', Session::getPluralNumber()),
            ],
            'formatters'      => [
                'name'     => 'raw_html',
                'status'   => 'raw_html',
                'priority' => 'raw_html',
                'items'    => 'raw_html',
            ],
            'entries'         => $entries,
            'total_number'    => count($entries),
            'filtered_number' => count($entries),
        ]);
    }
}
