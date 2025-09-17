<?php
/*
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

use GlpiPlugin\Eventsmanager\Event;
use GlpiPlugin\Eventsmanager\Config;

if (Plugin::isPluginActive("eventsmanager")) {
   $config = new Config();
   if (isset($_POST["update_config"])) {
        Session::checkRight("config", UPDATE);
        $config->update($_POST);
        Html::back();

   } else {
      Html::header(Event::getTypeName(), '', "helpdesk", Event::class, "config");
      $config->showConfigForm();
      Html::footer();
   }
} else {
   Html::header(__('Setup'), '', "config", "plugins");
   echo "<div class='alert alert-important alert-warning d-flex'>";
   echo "<b>".__('Please activate the plugin', 'eventsmanager')."</b></div>";
   Html::footer();
}
