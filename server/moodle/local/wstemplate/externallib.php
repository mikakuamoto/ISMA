<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External Web Service Template
 *
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot.'/calendar/lib.php');
require_once($CFG->dirroot.'/course/lib.php');

class local_wstemplate_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
//    public static function hello_world_parameters() {
//        return new external_function_parameters(
//                array('welcomemessage' => new external_value(PARAM_TEXT, 'The welcome message. By default it is "Hello world,"', VALUE_DEFAULT, 'Hello world, '))
//        );
//    }
    
     public static function hello_world_parameters() {
        return new external_function_parameters(
                array('welcomemessage' => new external_multiple_structure(
                       new external_single_structure(
                            array(
                                'courseid' => new external_value(PARAM_TEXT, 'group record id'),
                                'name' => new external_value(PARAM_TEXT, 'id of course'),
                                'description' => new external_value(PARAM_TEXT, 'multilang compatible name, course unique'),
                                'timestart' => new external_value(PARAM_TEXT, 'group description text'),
                            )
            ))
        ));
    }


    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function hello_world($welcomemessage = array()) {
        global $USER;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::hello_world_parameters(),
                array('welcomemessage' => $welcomemessage));

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }
        
//        //exemplo de array com eventos
//        $event1['courseid'] = '2';
//        $event1['name'] = 'aula 1';
//        $event1['description'] = 'descrição aula 1';
//        $event1['timestart'] = '2012;4;20;19;30;0';
//        $event2['courseid'] = '2';
//        $event2['name'] = 'aula 2';
//        $event2['description'] = 'descrição aula 2';
//        $event2['timestart'] = '2012;4;21;19;30;0';
//        $event3['courseid'] = '2';
//        $event3['name'] = 'aula 3';
//        $event3['description'] = 'descrição aula 3';
//        $event3['timestart'] = '2012;4;22;19;30;0';
//        
//        //calendario com todos os eventos
//        $calendario = array($event1, $event2, $event3);
//
        //pega cada evento e grava no banco
        for ($i=0;$i<sizeof($welcomemessage); $i++ ){
            $temp = $welcomemessage[$i]; 
            $newevent = new stdClass();
            $newevent->eventtype = 'course';
            $newevent->courseid = (int)$temp['courseid'];
            $newevent->name = $temp['name'];
            $newevent->description = $temp['description'];
            $timetemp = explode(';', $temp['timestart']);
            $newevent->timestart = make_timestamp((int)$timetemp[0],(int)$timetemp[1],(int)$timetemp[2],(int)$timetemp[3],(int)$timetemp[4],(int)$timetemp[5]);
            $newevent->timeduration = 90  * MINSECS;
            $newevent = new calendar_event($newevent);
            $newevent->update($newevent); 
        }
        return 'foi';
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function hello_world_returns() {
        return new external_value(PARAM_TEXT, 'The welcome message + user first name');
    }



}
