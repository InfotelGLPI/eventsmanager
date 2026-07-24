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
use Glpi\Application\View\TemplateRenderer;
use RSSFeed;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Rssimport
 */
class Rssimport extends CommonDBTM
{

   /**
    * @param int $nb
    *
    * @return string
    */
    static function getTypeName($nb = 0)
    {

        return __('Import RSS feeds for events manager', 'eventsmanager');
    }

    static function getIcon()
    {
        return Event::getIcon();
    }
   /**
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return string
    */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if ($item->getType() == 'RSSFeed') {
            return self::createTabEntry(_n('Event manager', 'Events manager', 2, 'eventsmanager'));
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
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        $rss = new self();
        if ($item->getType() == 'RSSFeed') {
            $idr = $item->getID();
            if (!$rss->getFromDBByCrit(['rssfeeds_id' => $idr])) {
                $id = $rss->add(['last_rssfeed_url'   => '',
                             'rssfeeds_id'        => $idr,
                             'use_with_plugin'    => '0',
                             'default_impact'     => '0',
                             'default_eventtype'  => '0',
                             'default_priority'   => '0',
                             'entities_id_import' => '0']);
                $rss->getFromDB($id);
            }
            $rss->showConfig($idr);
        }
        return true;
    }


   /**
    * @param  $item
    */
    function showConfig($idr)
    {

        TemplateRenderer::getInstance()->display('@eventsmanager/rssimport.html.twig', [
            'item'        => $this,
            'form_action' => $this->getFormURL(),
            'rssfeeds_id' => $idr,
        ]);
    }

   /**
    * Give localized information about 1 task
    *
    * @param $name of the task
    *
    * @return array of strings
    */
    static function cronInfo($name)
    {

        switch ($name) {
            case 'RssImport':
                return ['description' => __('Import RSS feeds for events manager', 'eventsmanager')];
        }
        return [];
    }

   /**
    * Execute 1 task manage by the plugin
    *
    * @param $task Object of CronTask class for log / stat
    *
    * @return integer
    *    >0 : done
    *    <0 : to be run again (not finished)
    *     0 : nothing to do
    */
    static function cronRssImport($task = null)
    {
        global $DB;

        $rssimport = new Rssimport();
        $event     = new Event();
        $rssfeed   = new RSSFeed();
        $origin    = new Origin();

        $iterator = $DB->request([
            'SELECT'    => ['glpi_plugin_eventsmanager_rssimports.*'],
            'DISTINCT'  => true,
            'FROM'      => 'glpi_plugin_eventsmanager_rssimports',
            'LEFT JOIN' => [
                'glpi_rssfeeds' => [
                    'ON' => [
                        'glpi_rssfeeds'                          => 'id',
                        'glpi_plugin_eventsmanager_rssimports'   => 'rssfeeds_id',
                    ],
                ],
            ],
            'WHERE'     => [
                'use_with_plugin' => 1,
                'is_active'       => 1,
                'have_error'      => 0,
            ],
        ]);

        foreach ($iterator as $data) {
            $id = $data['id'];
            if ($rssfeed->getFromDB($data['rssfeeds_id'])) {
                if (($feed = RSSFeed::getRSSFeed($rssfeed->fields['url'])) !== false) {
                    foreach ($feed->get_items(0, $rssfeed->fields['max_items']) as $item) {
                        if ($data['last_rssfeed_url'] != $item->get_link()) {
                            $input['date_creation'] = $item->get_date('Y-m-d H:i:s');
                            $input['name']          = $item->get_title();
                            $input['comment']       = strip_tags($item->get_content());
                            $input['priority']      = $data['default_priority'];
                            $input['impact']        = $data['default_impact'];
                            $input['eventtype']     = $data['default_eventtype'];
                            $input['entities_id']   = $data['entities_id_import'];
                            if ($origin->getFromDBByCrit(['itemtype' => Origin::RSS,
                                                  'items_id' => $data['rssfeeds_id']])) {
                                $input['plugin_eventsmanager_origins_id'] = $origin->getID();
                            }
                            $event->add($input);
                            $task->addVolume(1);
                        } else {
                            break;
                        }
                    }
                    $item = $feed->get_item(0);
                    $rssimport->update(['id'               => $id,
                                  'last_rssfeed_url' => $item->get_link()], 0);
                }
            }
        }
        return 1;
    }

    static function addSearchOptions($sopt = [])
    {

        $dbu = new DbUtils();

        $sopt[200]['table']         = 'glpi_plugin_eventsmanager_rssimports';
        $sopt[200]['field']         = 'use_with_plugin';
        $sopt[200]['name']          = __('Use this feed to create events', 'eventsmanager');
        $sopt[200]['datatype']      = 'bool';
        $sopt[200]['massiveaction'] = true;
        $sopt[200]['joinparams']    = ['jointype'  => 'child',
                                     'linkfield' => 'rssfeeds_id'];

        $sopt[201]['table']         = 'glpi_plugin_eventsmanager_rssimports';
        $sopt[201]['field']         = 'default_impact';
        $sopt[201]['name']          = __('Default impact', 'eventsmanager');
        $sopt[201]['datatype']      = 'specific';
        $sopt[201]['massiveaction'] = true;
        $sopt[201]['searchtype']    = 'equals';
        $sopt[201]['joinparams']    = ['jointype'  => 'child',
                                     'linkfield' => 'rssfeeds_id'];

        $sopt[202]['table']         = 'glpi_plugin_eventsmanager_rssimports';
        $sopt[202]['field']         = 'default_priority';
        $sopt[202]['name']          = __('Default priority', 'eventsmanager');
        $sopt[202]['datatype']      = 'specific';
        $sopt[202]['massiveaction'] = true;
        $sopt[202]['searchtype']    = 'equals';
        $sopt[202]['joinparams']    = ['jointype'  => 'child',
                                     'linkfield' => 'rssfeeds_id'];

        $sopt[203]['table']         = 'glpi_plugin_eventsmanager_rssimports';
        $sopt[203]['field']         = 'default_eventtype';
        $sopt[203]['name']          = __('Default event type', 'eventsmanager');
        $sopt[203]['datatype']      = 'specific';
        $sopt[203]['massiveaction'] = true;
        $sopt[203]['searchtype']    = 'equals';
        $sopt[203]['joinparams']    = ['jointype'  => 'child',
                                     'linkfield' => 'rssfeeds_id'];

        $sopt[204]['table']         = $dbu->getTableForItemType('Entity');
        $sopt[204]['field']         = 'name';
        $sopt[204]['linkfield']     = 'entities_id_import';
        $sopt[204]['name']          = __('Entity');
        $sopt[204]['datatype']      = 'itemlink';
        $sopt[204]['itemlink_type'] = 'Entity';
        $sopt[204]['massiveaction'] = false;
        $sopt[204]['joinparams']    = ['beforejoin'
                                     => ['table'      => 'glpi_plugin_eventsmanager_rssimports',
                                         'joinparams' => ['jointype' => 'child']]];

        return $sopt;
    }

   /**
    * display a value according to a field
    *
    * @since version 0.83
    *
    * @param $field     String         name of the field
    * @param $values    String / Array with the value to display
    * @param $options   Array          of option
    *
    * @return int|string string
    **/
    static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'default_impact':
                return CommonITILObject::getImpactName($values[$field]);
            case 'default_priority':
                return CommonITILObject::getPriorityName($values[$field]);
            case 'default_eventtype':
                return Event::getEventTypeName($values[$field]);
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

   /**
    * @since version 2.3.0
    *
    * @param $field
    * @param $name (default '')
    * @param $values (defaut '')
    * @param $options   array
    **/
    static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'default_impact':
                $options['name']  = $name;
                $options['value'] = $values[$field];
                return CommonITILObject::dropdownImpact($options);

            case 'default_priority':
                $options['name']  = $name;
                $options['value'] = $values[$field];
                return CommonITILObject::dropdownPriority($options);

            case 'default_eventtype':
                $options['name']  = $name;
                $options['value'] = $values[$field];
                return Event::dropdownType($options);
        }
        return parent::getSpecificValueToSelect($field, $values, $options);
    }
}
