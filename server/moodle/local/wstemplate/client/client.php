<?php
// This client for local_wstemplate is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

/**
 * XMLRPC client for Moodle 2 - local_wstemplate
 *
 * This script does not depend of any Moodle code,
 * and it can be called from a browser.
 *
 * @authorr Jerome Mouneyrac
 */

/// MOODLE ADMINISTRATION SETUP STEPS
// 1- Install the plugin
// 2- Enable web service advance feature (Admin > Advanced features)
// 3- Enable XMLRPC protocol (Admin > Plugins > Web services > Manage protocols)
// 4- Create a token for a specific user (Admin > Plugins > Web services > Manage tokens)
// 5- Run this script directly from your browser: you should see 'Hello, FIRSTNAME'

/// SETUP - NEED TO BE CHANGED
$token = 'bf59c1d0a252d1dc36a2bc5dd929f63d';
$domainname = 'http://localhost:81';

/// FUNCTION NAME
$functionname = 'local_wstemplate_hello_world';

/// PARAMETERS
        $event1['courseid'] = '2';
        $event1['name'] = 'aula 1';
        $event1['description'] = 'descrição aula 1';
        $event1['timestart'] = '2012;3;30;19;30;0';
//        $event2['courseid'] = '2';
//        $event2['name'] = 'aula 2';
//        $event2['description'] = 'descrição aula 2';
//        $event2['timestart'] = '2012;5;21;19;30;0';
//        $event3['courseid'] = '2';
//        $event3['name'] = 'aula 3';
//        $event3['description'] = 'descrição aula 3';
//        $event3['timestart'] = '2012;5;22;19;30;0';
        
        //calendario com todos os eventos
        $calendario = array($event1);


///// XML-RPC CALL
header('Content-Type: text/plain');
$serverurl = $domainname . '/webservice/xmlrpc/server.php'. '?wstoken=' . $token;
require_once('./curl.php');
$curl = new curl;
$post = xmlrpc_encode_request($functionname, array($calendario));
$resp = xmlrpc_decode($curl->post($serverurl, $post));
print_r($resp);
