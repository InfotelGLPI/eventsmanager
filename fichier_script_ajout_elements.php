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

///init Session
define('DO_NOT_CHECK_HTTP_REFERER', 1);
ini_set('session.use_cookies', 0);

include('../../inc/includes.php');

//ini_set("memory_limit", "-1");
//ini_set("max_execution_time", "0");

//AUthentication
$ch      = curl_init();
$api_url = "http://172.16.6.4/glpi92/apirest.php/";
//API token field (user)
$user_token = "n4nKdfrNA2tase9gihOS3EWXw5EE3Sl6uNocWxrX";
$app_token  = "1b6MGWga8xcSrw2m7p9gvuovYmy7sIYymwE5W2zp";
$url        = $api_url . "initSession?Content-Type=%20application/json&app_token=" . $app_token . "&user_token=" . $user_token;
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$json = curl_exec($ch);
$json = json_decode($json, true);
print_r($json);
//curl_close ($ch);

if ($json['session_token']) {
   $session_token = $json['session_token'];
   /////////////////////////////////////////////////////////////////////////////////////////

   $input      = '{"input": {"name": "Alert 1",
                             "entities_id": "1",
                             "status": "1",
                             "eventtype": "1",
                             "plugin_eventsmanager_origins_id": "3",
                             "impact": "1",
                             "priority":"1",
                             "comment": "In conplurium in admissum vultu modo ruinas quod conplurium quosdam veritate Constanti iussa in ingenuorum."}}';
   $headers    = [
      'Content-Type: application/json',
      'Session-Token:' . $session_token,
      'App-Token:' . $app_token];
   $ch         = curl_init();
   $url_events = $api_url . "/GlpiPlugin\Eventsmanager\Event/";
   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
   curl_setopt($ch, CURLOPT_URL, $url_events);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
   curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
   $events = curl_exec($ch);
   $events = json_decode($events, true);
   printf("<br>");
   print_r($events);
   if ($events != null) {
      printf("<br>");
      printf("Events added");
   }

   ////////////////////////////////////////////////////////////////////////////////////
   //Kill Session
   $headers = [
      'Content-Type: application/json',
      'Session-Token:' . $session_token,
      'App-Token:' . $app_token];
   $ch      = curl_init();
   curl_setopt($ch, CURLOPT_URL, $api_url . "killSession/");
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $end = curl_exec($ch);
   curl_close($ch);
   printf("<br>");
   printf("End");
} else {
   printf("Authentication Error");
}

