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

namespace GlpiPlugin\Eventsmanager;

use Ajax;
use CommonDBTM;
use CommonDropdown;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use MailCollector;
use RSSFeed;

class Origin extends CommonDropdown
{
    public const Collector = 1;
    public const RSS       = 2;
    public const Api       = 3;
    public const Others    = 4;

    public $dohistory         = true;
    public static $rightname         = 'plugin_eventsmanager';
    public $can_be_translated = false;

    /**
     * Returns the type name with consideration of plural
     *
     * @param number $nb Number of item(s)
     *
     * @return string Itemtype name
     */
    public static function getTypeName($nb = 0)
    {
        return _n('Event origin', 'Event origins', $nb, 'eventsmanager');
    }

    public function getAdditionalFields()
    {

        return [['name'  => 'requesttypes_id',
            'label' => __('Request source'),
            'type'  => 'dropdownValue',
            'list'  => true],
            ['name'  => 'itemtype',
                'label' => __('Item type'),
                'type'  => 'specific',
                'list'  => true],
            ['name'  => 'items_id',
                'label' => __('Item'),
                'type'  => 'specific',
                'list'  => true],
        ];
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
            'id'       => '9',
            'table'    => 'glpi_requesttypes',
            'field'    => 'name',
            'name'     => __('Request source'),
            'datatype' => 'dropdown',
        ];

        $tab[] = [
            'id'            => '4',
            'table'         => $this->getTable(),
            'field'         => 'itemtype',
            'name'          => __('Item type'),
            'massiveaction' => false,
            'searchtype'    => 'equals',
            'datatype'      => 'specific',
        ];

        $tab[] = [
            'id'               => '13',
            'table'            => $this->getTable(),
            'field'            => 'items_id',
            'name'             => __('Item'),
            'datatype'         => 'specific',
            'additionalfields' => ['itemtype'],
            'nosearch'         => true,
            'massiveaction'    => false,
        ];

        return $tab;
    }

    /**
     * Display specific fields
     *
     * @global  $CFG_GLPI
     *
     * @param   $ID
     * @param   $field
     */
    public function displaySpecificTypeField($ID, $field = [], array $options = [])
    {

        switch ($field['name']) {
            case 'itemtype':
                self::dropdownItemOrigin($ID, $this->fields['itemtype']);
                break;
            case 'items_id':
                self::selectItems($this);
                break;
        }
    }

    /**
     * @since version 0.84
     *
     * @param $field
     * @param $values
     * @param $options   array
     **/
    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'items_id':
                if (isset($values['itemtype'])
                && !empty($values['itemtype'])) {
                    return self::getItemOrigin($field, $values);
                }
                break;
            case 'itemtype':
                return self::getItemtypeOrigin($values[$field]);
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    /**
     * @since version 0.84
     *
     * @param $field
     * @param $name (default '')
     * @param $values (default '')
     * @param $options   array
     *
     * @return string
     **/
    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;
        switch ($field) {
            case 'itemtype':
                $options['value'] = $values[$field];
                return Dropdown::showFromArray($name, self::getAllItemOriginArray(), $options);
                break;
        }

        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }

    /**
     * Show the Item Origin dropdown
     *
     * @param array $options
     *
     */
    public function dropdownItemOrigin($ID, $value = 0)
    {
        global $CFG_GLPI;

        if ($ID > 0) {
            TemplateRenderer::getInstance()->display('@eventsmanager/origin_itemtype_readonly.html.twig', [
                'itemtype_label' => self::getItemtypeOrigin($this->fields['itemtype']),
                'itemtype'       => $this->fields['itemtype'],
            ]);
        } else {
            $rand = Dropdown::showFromArray('itemtype', self::getAllItemOriginArray(), ['display_emptychoice' => true]);

            $params = ['itemtype' => '__VALUE__',
                'id'       => $ID];
            Ajax::updateItemOnSelectEvent(
                "dropdown_itemtype$rand",
                "span_itemtype",
                PLUGIN_EVENTMANAGER_WEBDIR . "/ajax/dropdownOriginItem.php",
                $params,
            );
        }
    }


    public static function selectItems(CommonDBTM $origin)
    {

        $dropdown = self::dropdownItems(
            $origin->fields['itemtype'],
            ['value' => $origin->fields['items_id']],
        );

        TemplateRenderer::getInstance()->display('@eventsmanager/origin_item_span.html.twig', [
            'dropdown' => $dropdown,
        ]);
    }


    /**
     * Label "<source type> - <item>" of an origin (empty when the origin does not exist)
     */
    public static function renderItemLabel(int $origins_id): string
    {
        $origin = new self();
        $data   = ['itemtype_label' => '', 'item_name' => ''];
        if ($origins_id > 0 && $origin->getFromDB($origins_id)) {
            $data = [
                'itemtype_label' => self::getItemtypeOrigin($origin->fields['itemtype']),
                'item_name'      => self::getItemOrigin('items_id', [
                    'itemtype' => $origin->fields['itemtype'],
                    'items_id' => $origin->fields['items_id'],
                ]),
            ];
        }

        return TemplateRenderer::getInstance()->render('@eventsmanager/origin_item_label.html.twig', $data);
    }

    /**
     * Item selector of a source type (mail collector, RSS feed...)
     *
     * @param mixed                $itemtype one of the Origin source type constants
     * @param array<string, mixed> $options  options of the core dropdown
     */
    public static function dropdownItems($itemtype, $options = []): string
    {

        $p['name']    = 'items_id';
        $p['values']  = [];

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }

        $p['display'] = false;
        switch ($itemtype) {
            case self::Collector:
                $html = (string) MailCollector::dropdown($p);
                break;
            case self::RSS:
                $html = (string) RSSFeed::dropdown($p);
                break;
            case self::Api:
            case self::Others:
                $html = htmlescape(__('None'));
                break;
            default:
                $html = '';
        }

        return $html;
    }

    /**
     * Function get the Item type Origin
     *
     * @return  string
     */
    public static function getItemtypeOrigin($value)
    {
        $data = self::getAllItemOriginArray();
        return $data[$value] ?? '';
    }

    /**
     * Function get the Item Origin
     *
     * @return  string
     */
    public static function getItemOrigin($field, $values)
    {

        switch ($values['itemtype']) {
            // can() rather than getFromDB(): a mail collector or an RSS feed is configuration,
            // whose name (often the mailbox address) the plugin right alone must not disclose
            case self::Collector:
                $mail = new MailCollector();
                return $mail->can((int) $values[$field], READ) ? $mail->getName() : '';
            case self::RSS:
                $rss = new RSSFeed();
                return $rss->can((int) $values[$field], READ) ? $rss->getName() : '';
            case self::Api:
                return __('None');
            case self::Others:
                return __('None');
        }
    }

    /**
     * Get the ItemOrigin list
     *
     * @return  array
     */
    public static function getAllItemOriginArray()
    {

        // To be overridden by class
        $tab = [0               => Dropdown::EMPTY_VALUE,
            self::Collector => __('Mails receiver'),
            self::RSS       => _n('RSS feed', 'RSS feeds', 1),
            self::Api       => __('Rest API'),
            self::Others    => __('Others')];

        return $tab;
    }
}
