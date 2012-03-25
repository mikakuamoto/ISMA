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
    public static function hello_world_parameters() {
        return new external_function_parameters(
                array('welcomemessage' => new external_value(PARAM_TEXT, 'The welcome message. By default it is "Hello world,"'))
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function hello_world($welcomemessage = 'hello') {
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
        
     $string = '<?xml version="1.0" encoding="UTF-8"?>
                        <calendar>
                                <event>
                                        <eventtype>course</eventtype>
                                        <name>P1</name>
                                        <description>Primeira prova</description>
                                </event>

                        </calendar>';
        
     $xml = simplexml_load_string($string);

    foreach($xml->event as $event){
        $newevent = new stdClass();
        $newevent->eventtype = $event->eventtype;
        $newevent->courseid = 2;
        $newevent->name = $event->name;
        $newevent->description = $event->description;
        $newevent->timestart = make_timestamp(2012, 4, 29, 19, 30, 0);
        $newevent->timeduration = 90  * MINSECS;
        $newevent = new calendar_event($newevent);
        $newevent->update($newevent);      

    }
//        $event = new stdClass();
//        $event->eventtype = 'course';
//        $event->courseid = 2;
//        $event->name = 'psicologia';
//        $event->description = 'evento de curso de psicologia';
//        $event->timestart = make_timestamp(2012, 3, 21, 21, 15, 0);
//        $event->timeduration = 90 * MINSECS;
//        $event = new calendar_event($event);
//        $event->update($event);
        return  "foi";
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function hello_world_returns() {
        return new external_value(PARAM_TEXT, 'The welcome message + user first name');
    }



}
