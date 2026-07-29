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

use Glpi\Exception\Http\AccessDeniedHttpException;
use Glpi\Exception\Http\BadRequestHttpException;
use GlpiPlugin\Eventsmanager\Ticket;

Session::checkLoginUser();

$ticket = new Ticket();
if (isset($_POST["add"])) {
   $ticket->check(-1, CREATE, $_POST);

   // check(-1, CREATE) only validates the plugin link-creation right, not visibility of the
   // attacker-supplied target ticket; require READ on the core ticket before linking it so a
   // ticket from an unreachable entity cannot be linked (and later disclosed via the event).
   $core_ticket = new \Ticket();
   if (!$core_ticket->can((int) ($_POST['tickets_id'] ?? 0), READ)) {
       throw new AccessDeniedHttpException();
   }

   $ticket->add($_POST);
   Html::back();

}

throw new BadRequestHttpException();

