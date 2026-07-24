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
use Glpi\Application\View\TemplateRenderer;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class Mailimport
 */
class Mailimport extends CommonDBTM {

   /**
    * @param int $nb
    *
    * @return string
    */
   static function getTypeName($nb = 0) {

      return __('Import mails for events manager', 'eventsmanager');
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
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == 'MailCollector') {
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
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      $mail = new self();
      if ($item->getType() == 'MailCollector') {
         $idr = $item->getID();
         if (!($res = $mail->getFromDBByCrit(['mailcollectors_id' => $idr]))) {
            $id = $mail->add(['mailcollectors_id' => $idr,
                              'default_impact'    => '0',
                              'default_eventtype' => '0',
                              'default_priority'  => '0']);
            $mail->getFromDB($id);
         }
         $mail->showConfig($idr);
      }
      return true;
   }


   /**
    * @param  $item
    */
   function showConfig($idr) {

      TemplateRenderer::getInstance()->display('@eventsmanager/mailimport.html.twig', [
          'item'              => $this,
          'form_action'       => $this->getFormURL(),
          'mailcollectors_id' => $idr,
      ]);
   }
}
