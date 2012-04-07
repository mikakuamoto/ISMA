<?php

/**
 * Web service local plugin isma external functions implementation.
 *
 * @package		localwsisma
 * @author		Mika Kuamoto - Paulo Silveira  
 */
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

class local_isma_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function insert_events_parameters() {
        return new external_function_parameters(
            array('calendar' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                            'courseid' => new external_value(PARAM_INT, 'Course Id'),
                                            'name' => new external_value(PARAM_TEXT, 'Event Name'),
                                            'description' => new external_value(PARAM_TEXT, 'Event Description'),
                                            'timestart' => new external_value(PARAM_TEXT, 'Event Date'),
                                        )
                                    )
                                )
            )
        );
    }

    /**
     * Add events into Moodle's calendar.
     * @return String returns a success message
     */
    public static function insert_events($calendar = array()) {
        global $USER;

        //Parameter validation
        $params = self::validate_parameters(self::insert_events_parameters(), array('calendar' => $calendar));

        //Context validation
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Capability checking
        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }

        //Add each event into data base
        for ($i = 0; $i < sizeof($calendar); $i++) {
            $temp = $calendar[$i];
            $newevent = new stdClass();
            
            $newevent->eventtype = 'course';
            $newevent->courseid = $temp['courseid'];
            $newevent->name = $temp['name'];
            $newevent->description = $temp['description'];
            
            $timetemp = explode(';', $temp['timestart']);
            $newevent->timestart = make_timestamp((int) $timetemp[0], (int) $timetemp[1], (int) $timetemp[2], (int) $timetemp[3], (int) $timetemp[4], (int) $timetemp[5]);
            $newevent->timeduration = 90 * MINSECS;
            
            $newevent = new calendar_event($newevent);
            if (!calendar_add_event_allowed($newevent)) {
                print_error('nopermissions');
            }
            
            $newevent->update($newevent);
        }               
        
        return "O Calendário da disciplina foi atualizado com sucesso!!";
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function insert_events_returns() {
        return new external_value(PARAM_TEXT, 'Returns a sucess message');
    }
    
    public static function update_events_parameters(){
        return new external_function_parameters(
            array(
                'calendar' => new external_multiple_structure(
                                    new external_single_structure(
                                            array(
                                                'courseid' => new external_value(PARAM_INT, 'Course Id'), //depois este campo vai sair
                                                'name' => new external_value(PARAM_TEXT, 'Event name'),
                                                'description' => new external_value(PARAM_TEXT, 'Event description'),
                                                'timestart' => new external_value(PARAM_TEXT, 'Event date'),
                                            )
                                    )
                                ),
                'course' => new external_value (PARAM_INT, 'Course description'), //agora ta o id depois mudar pra descrição 
                                                                                   //ja q o cliente nao sabe o id
                )
        );
    }
    
    public static function update_events($calendar, $course){
        global $USER;

        //Parameter validation
        $params = self::validate_parameters(self::update_events_parameters(), array('calendar' => $calendar, 'course' => $course));

        //Context validation
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Capability checking
        //ARRUMAR ESSE CODIGO COLOCAR OUTRA CAPACIDADE
        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }
        
        
        //PEGA TODOS OS EVENTOS DO CALENDARIO DE UM CURSO
        //VER COMO FICA ESSA PARTE DE DATA INICIO E FIM
        $events = array();
        $events = calendar_get_events(make_timestamp(2012,04,03),make_timestamp(2012,05,10),false,false,$course,false,false);
        
        for ($i = 0; $i < sizeof($calendar); $i++) {
            $temp = $calendar[$i];
            $newevent = new stdClass();

            $newevent->eventtype = 'course';
            $newevent->courseid = $temp['courseid'];
            $newevent->name = $temp['name'];
            $newevent->description = $temp['description'];

            $timetemp = explode(';', $temp['timestart']);
            $newevent->timestart = make_timestamp((int) $timetemp[0], (int) $timetemp[1], (int) $timetemp[2], (int) $timetemp[3], (int) $timetemp[4], (int) $timetemp[5]);
            $newevent->timeduration = 90 * MINSECS;

            //COMPARA OS EVENTOS PASSADOS POR PARAMETRO COM OS QUE JA ESTAO NO CALENDARIO PELA DATA DO EVENTO
            //SE FOR IGUAL ELE PEGA O ID E COLOCA NO EVENTO PARA ATUALIZAR
            foreach ($events as $event){           
                if($event->timestart == $newevent->timestart){
                    $newevent->id = $event->id;
                    $newevent = new calendar_event($newevent);
                    $newevent->update($newevent);
                }
            }
        }     
        return 'O Calendario da disciplina foi atualizado com sucesso';
    }
    
    public static function update_events_returns(){
        return new external_value(PARAM_TEXT, 'Returns a sucess message');
    }
}
