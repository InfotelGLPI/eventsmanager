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

use CommonDBTM;
use CommonGLPI;
use Glpi\Application\View\TemplateRenderer;
use MailCollector;

/**
 * Class Mailimport
 */
class Mailimport extends CommonDBTM
{
    // Without a rightname, every can*() of the class answered false and the configuration
    // could never be saved, not even by a super-admin
    public static $rightname = 'plugin_eventsmanager';

    /*
     * The table has no entities_id: each item right replays the right on the mail collector
     * the row configures. can() has already checked the plugin right before calling these.
     */
    private function canOnCollector(int $right): bool
    {
        $collector = new MailCollector();
        $id        = (int) ($this->fields['mailcollectors_id'] ?? 0);

        return $id > 0 && $collector->can($id, $right);
    }

    public function canViewItem(): bool
    {
        return $this->canOnCollector(READ);
    }

    public function canCreateItem(): bool
    {
        return $this->canOnCollector(UPDATE);
    }

    public function canUpdateItem(): bool
    {
        return $this->canOnCollector(UPDATE);
    }

    public function canPurgeItem(): bool
    {
        return $this->canOnCollector(UPDATE);
    }

    /**
     * @param int $nb
     *
     * @return string
     */
    public static function getTypeName($nb = 0)
    {

        return __('Import mails for events manager', 'eventsmanager');
    }

    public static function getIcon()
    {
        return Event::getIcon();
    }

    /**
     * @param CommonGLPI $item
     * @param int        $withtemplate
     *
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if ($item->getType() == 'MailCollector' && self::canUpdate()) {
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
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        // ajax/common.tabs.php reaches this method without calling getTabNameForItem()
        if (!self::canUpdate()) {
            return false;
        }

        $mail = new self();
        if ($item->getType() == 'MailCollector') {
            $idr = $item->getID();
            // Read-only path: the defaults are built in memory, the row is created on the
            // first save (front/mailimport.form.php), not by merely viewing the tab
            if (!$mail->getFromDBByCrit(['mailcollectors_id' => $idr])) {
                $mail->getEmpty();
                $mail->fields['mailcollectors_id'] = $idr;
            }
            $mail->showConfig($idr);
        }
        return true;
    }


    /**
     * @param  $item
     */
    public function showConfig($idr)
    {

        TemplateRenderer::getInstance()->display('@eventsmanager/mailimport.html.twig', [
            'item'              => $this,
            'form_action'       => $this->getFormURL(),
            'mailcollectors_id' => $idr,
        ]);
    }
}
