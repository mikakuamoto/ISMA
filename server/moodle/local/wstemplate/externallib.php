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
                array('welcomemessage' => new external_value(PARAM_TEXT, 'The welcome message. By default it is "Hello world,"', VALUE_DEFAULT, 'Hello world, '))
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function hello_world($welcomemessage = 'Hello world, ') {
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

        $event1["eventtype"] = 'course';
        $event1["courseid"] = 2;
        $event1["name"] = 'P1';
        $event1["description"] = 'Primeira prova';
        //$event1["timestart"] = make_timestamp(2012, 3, 20, 19, 30, 0);
        $event1["timeduration"] = 90;

//        $event2["eventtype"] = 'course';
//        $event2["courseid"] = 2;
//        $event2["name"] = 'P2';
//        $event2["description"] = 'Segunda prova';
//        //$event2["timestart"] = make_timestamp(2012, 3, 20, 21, 15, 0);
//        $event2["timeduration"] = 90;
//
//        $event3["eventtype"] = 'course';
//        $event3["courseid"] = 2;
//        $event3["name"] = 'P3';
//        $event3["description"] = 'Terceira prova'; 
//        //$event3["timestart"] = make_timestamp(2012, 3, 21, 19, 30, 0);
//        $event3["timeduration"] = 90;

        $allEvents = Array($event1);
        
        
        for($i=0;$i< count($allEvents);$i++){
            $temp = $allEvents[i];
            $event = new stdClass();
            $event->eventtype = $temp["eventtype"];
            $event->courseid = 2;
            $event->name = $temp["name"];
            $event->description = $temp["description"];
            $event->timestart = make_timestamp(2012, 3, 22, 19, 30, 0);
            //$event->timestart = $temp["timestart"];
            $event->timeduration = 90  * MINSECS;
            $event = new calendar_event($event);
            $event->update($event);
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
        return $params['welcomemessage'] . $USER->firstname ;;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function hello_world_returns() {
        return new external_value(PARAM_TEXT, 'The welcome message + user first name');
    }



}
