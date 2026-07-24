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
use DbUtils;
use Glpi\Application\View\TemplateRenderer;
use Session;
use Toolbox;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/// Class Event_Comment
class Event_Comment extends CommonDBTM
{
    public static function getTypeName($nb = 0)
    {
        return _n('Comment', 'Comments', $nb);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $nb  = 0;
        $dbu = new DbUtils();
        if ($_SESSION['glpishow_count_on_tabs']) {
            $where = [];
            $where = [
                'plugin_eventsmanager_events_id' => $item->getID(),
            ];

            $nb = $dbu->countElementsInTable(
                'glpi_plugin_eventsmanager_events_comments',
                $where
            );
        }
        return self::createTabEntry(self::getTypeName($nb), $nb);
    }

    public static function getIcon()
    {
        return "ti ti-message-2";
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showForItem($item, $withtemplate);
        return true;
    }

    /**
     * Show linked items of a event
     *
     * @param $item                     CommonDBTM object
     * @param $withtemplate    integer  withtemplate param (default 0)
     **/
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        $event_id = $item->getID();
        $canedit  = $item->can($event_id, UPDATE);

        $comments = self::getCommentsForEvent($event_id);

        TemplateRenderer::getInstance()->display('@eventsmanager/event_comments.html.twig', [
            'canedit'  => $canedit,
            'url'      => Toolbox::getItemTypeFormURL(__CLASS__),
            'event_id' => $event_id,
            'comments' => self::prepareCommentsForDisplay($comments, $canedit),
        ]);
    }

    /**
     * Precompute display data for comments so the template only renders values.
     *
     * @param array   $comments Comments (as returned by getCommentsForEvent)
     * @param boolean $canedit  Whether the current user can edit the event
     *
     * @return array
     */
    private static function prepareCommentsForDisplay(array $comments, bool $canedit): array
    {
        foreach ($comments as &$comment) {
            $comment['_can_edit'] = $canedit
                && (Session::getLoginUserID() == $comment['users_id']);

            if (!empty($comment['answers'])) {
                $comment['answers'] = self::prepareCommentsForDisplay($comment['answers'], $canedit);
            }
        }
        unset($comment);

        return $comments;
    }

    /**
     * Gat all comments for specified event entry
     *
     * @param integer $plugin_eventsmanager_events_id event entry ID
     * @param integer $parent Parent ID (defaults to 0)
     *
     * @return array
     */
    public static function getCommentsForEvent($event_id, $parent = null)
    {
        global $DB;

        $where = [
            'plugin_eventsmanager_events_id' => $event_id,
            'parent_comment_id'              => $parent,
        ];

        $db_comments = $DB->request(['FROM' => 'glpi_plugin_eventsmanager_events_comments'] +
            $where + ['ORDER' => 'id ASC']
        );

        $comments = [];
        foreach ($db_comments as $db_comment) {
            $db_comment['answers'] = self::getCommentsForEvent($event_id, $db_comment['id']);
            $comments[]            = $db_comment;
        }

        return $comments;
    }

    /**
     * Purge child comments (answers) when a parent comment is deleted, so no
     * orphaned answers remain hidden in the database.
     *
     * @return void
     */
    public function cleanDBonPurge()
    {
        // Purge direct answers; their own answers are handled recursively by
        // this same hook when each child is deleted.
        $child = new self();
        $answers = $child->find([
            'plugin_eventsmanager_events_id' => $this->fields['plugin_eventsmanager_events_id'],
            'parent_comment_id'              => $this->getID(),
        ]);
        foreach ($answers as $data) {
            $child->delete(['id' => $data['id']], true);
        }
    }

    public function prepareInputForAdd($input)
    {
        if (!isset($input["users_id"])) {
            $input["users_id"] = 0;
            if ($uid = Session::getLoginUserID()) {
                $input["users_id"] = $uid;
            }
        }

        return $input;
    }
}
