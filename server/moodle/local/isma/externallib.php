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
require_once($CFG->dirroot . '/mod/forum/lib.php');

class local_isma_external extends external_api {
    
    /**
     * Add events of a course to its calendar
     * @param array $calendar
     * @return String returns a success message
     */
    public static function insert_events($calendar, $msgforum, $flagemail) {
        global $USER;

        //Validação dos parâmetros
        $params = self::validate_parameters(self::insert_events_parameters(), 
                    array('calendar' => $calendar, 'msgforum' => $msgforum, 'flagemail' => $flagemail));

        //Validação do contexto
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Validação da capacidade
        if (!has_capability('moodle/calendar:manageentries', $context)) {
            throw new moodle_exception('nopermissiontoupdatecalendar');
        }
        
        //Adiciona todos os eventos no calendário
        for ($i = 0; $i < sizeof($calendar); $i++) {
            $temp = $calendar[$i];
            $newevent = new stdClass();
            $newevent->eventtype = 'course'; //Tipo do evento
            $newevent->courseid = $temp['courseid'];
            $newevent->name = $temp['name'];
            $newevent->description = $temp['description'];
            $timestarttemp = explode(';', $temp['timestart']);
            $newevent->timestart = make_timestamp((int) $timestarttemp[0], (int) $timestarttemp[1], (int) $timestarttemp[2], (int) $timestarttemp[3], (int) $timestarttemp[4]);
            $timeendtemp = explode(';', $temp['timedurationuntil']);
            $newevent->timedurationuntil = make_timestamp((int) $timeendtemp[0], (int) $timeendtemp[1], (int) $timeendtemp[2], (int) $timeendtemp[3], (int) $timeendtemp[4]);
            $newevent->timeduration = $newevent->timedurationuntil- $newevent->timestart; //Duração do evento
            $newevent = new calendar_event($newevent);
            $newevent->update($newevent);
        }   
        
        //Cria um tópico no fórum de notícias sobre a atualização do calendário
        $firstevent = $calendar[0];
        $courseid = $firstevent['courseid'];
        $forum = forum_get_course_forum($courseid, 'news'); //Procura o Id do fórum de notícias
        $discussion = new stdClass();
        $discussion->forum = $forum->id;
        $discussion->course = $courseid;
        $discussion->name = 'Atualização do Calendario'; //Título padrão do tópico
        if ($msgforum != ''){
            $discussion->message = $msgforum;
        } else {
            $discussion->message = 'O Calendario foi alterado'; //Mensagem padrão caso o usuário não mande
        }
        $discussion->messageformat = FORMAT_HTML;
        $discussion->messagetrust = 1;
        $discussion->mailnow = $flagemail; //Flag para indicar se deseja ou não enviar email aos alunos
        $discussion->attachments = null;
        $discussionid = forum_add_discussion($discussion);
        
        return 'Os eventos foram inseridos com sucesso!!';
    }
    
    /**
     * Update events of a course into its calendar
     * @param array $calendar
     * @return String returns a success message
     */
    public static function update_events($calendar, $msgforum, $flagemail){
        global $USER;

        //Validação dos parâmetros
        $params = self::validate_parameters(self::update_events_parameters(), 
                    array('calendar' => $calendar, 'msgforum' => $msgforum, 'flagemail' => $flagemail));

        //Validação do contexto
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        ///validação da capacidade
        if (!has_capability('moodle/calendar:manageentries', $context)) {
            throw new moodle_exception('nopermissiontoupdatecalendar');
        }
        
        //Pega todos os eventos do calendário do curso        
        $firstevent = $calendar[0];
        $courseid = $firstevent['courseid'];
        $events = array();
        $events = calendar_get_events_by_course($courseid);
        
        //Atualiza os eventos
        for ($i = 0; $i < sizeof($calendar); $i++) {
            $temp = $calendar[$i];
            $newevent = new stdClass();
            $newevent->eventtype = 'course'; //Tipo do evento
            $newevent->courseid = $temp['courseid'];
            
            //Verifica se precisa alterar o nome
            if(($temp['newname']) != ''){
                $newevent->name = $temp['newname'];
            } else{
                $newevent->name = $temp['name'];
            }
            
            //Verifica se precisa alterar a descrição
            if(($temp['newdescription']) != ''){
                $newevent->description = $temp['newdescription'];
            } else{
                $newevent->description = $temp['description'];
            }
            
            //Verifica se precisa alterar a data de início
            $timestarttemp = explode(';', $temp['timestart']);
            $timestart = make_timestamp((int) $timestarttemp[0], (int) $timestarttemp[1], (int) $timestarttemp[2], (int) $timestarttemp[3], (int) $timestarttemp[4]);
            if(($temp['newtimestart']) != ''){
                $timestarttemp = explode(';', $temp['newtimestart']);
            }
            $newevent->timestart = make_timestamp((int) $timestarttemp[0], (int) $timestarttemp[1], (int) $timestarttemp[2], (int) $timestarttemp[3], (int) $timestarttemp[4]);
            
            //Verifica se precisa alterar a data de término
            $timeendtemp = explode(';', $temp['timedurationuntil']);
            $timedurationuntil = make_timestamp((int) $timeendtemp[0], (int) $timeendtemp[1], (int) $timeendtemp[2], (int) $timeendtemp[3], (int) $timeendtemp[4]);
            if(($temp['newtimedurationuntil']) != ''){
                $timeendtemp = explode(';', $temp['newtimedurationuntil']);
            }
            $newevent->timedurationuntil = make_timestamp((int) $timeendtemp[0], (int) $timeendtemp[1], (int) $timeendtemp[2], (int) $timeendtemp[3], (int) $timeendtemp[4]);
            
            $newevent->timeduration = $newevent->timedurationuntil- $newevent->timestart;

            //Compara os eventos passados por parâmetro com os que já estão no calendário pela data do
            //evento, pelo nome e pela descrição, se for igual, pega o id e coloca no novo evento para
            //atualizar.
            foreach ($events as $event){           
                if($event->name == $temp['name'] && $event->description == $temp['description'] && 
                        $event->timestart == $timestart && $event->timeduration == $timedurationuntil - $timestart){
                    $newevent->id = $event->id;
                    $newevent = new calendar_event($newevent);
                    $newevent->update($newevent);
                }
            }
        }
        
        //Cria um tópico no fórum de notícias sobre a atualização do calendário
        $forum = forum_get_course_forum($courseid, 'news'); //Procura o Id do fórum de notícias
        $discussion = new stdClass();
        $discussion->forum = $forum->id;
        $discussion->course = $courseid;
        $discussion->name = 'Atualização do Calendario'; //Título padrão do tópico
        if ($msgforum != ''){
            $discussion->message = $msgforum;
        } else {
            $discussion->message = 'O Calendario foi alterado'; //Mensagem padrão caso o usuário não mande
        }
        $discussion->messageformat = FORMAT_HTML;
        $discussion->messagetrust = 1;
        $discussion->mailnow = $flagemail; //Flag para indicar se deseja ou não enviar email aos alunos
        $discussion->attachments = null;
        $discussionid = forum_add_discussion($discussion);
        
        return 'Os eventos foram atualizados com sucesso!!';
    }
    
    /**
     * Remove events of a course from its calendar
     * @param array $calendar
     * @return String returns a success message
     */
    public static function remove_events($calendar, $msgforum, $flagemail){
        global $USER;

        //Validação dos parâmetros
        $params = self::validate_parameters(self::remove_events_parameters(), 
                    array('calendar' => $calendar, 'msgforum' => $msgforum, 'flagemail' => $flagemail));

        //Validação do contexto
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Validação da capacidade
        if (!has_capability('moodle/calendar:manageentries', $context)) {
            throw new moodle_exception('nopermissiontoupdatecalendar');
        }
        
        //Pega todos os eventos do calendário do curso
        $firstevent = $calendar[0];
        $courseid = $firstevent['courseid'];
        $events = array();
        $events = calendar_get_events_by_course($courseid);
        
        for ($i = 0; $i < sizeof($calendar); $i++) {
            $temp = $calendar[$i];
            $timestarttemp = explode(';', $temp['timestart']);
            $timestart = make_timestamp((int) $timestarttemp[0], (int) $timestarttemp[1], (int) $timestarttemp[2], (int) $timestarttemp[3], (int) $timestarttemp[4]);
            $timeendtemp = explode(';', $temp['timedurationuntil']);
            $timedurationuntil = make_timestamp((int) $timeendtemp[0], (int) $timeendtemp[1], (int) $timeendtemp[2], (int) $timeendtemp[3], (int) $timeendtemp[4]);
           
            //Compara os eventos passados por parâmetro com os que já estão no calendário pela data do
            //evento, pelo nome e pela descrição, se for igual, remove.
            foreach ($events as $event){
                if($event->timestart == $timestart && $event->timeduration == $timedurationuntil - $timestart && 
                        $event->name == $temp['name'] && $event->description == $temp['description']) {                    
                    $event = new calendar_event($event);
                    $event->delete(false);
                }
            }
        }    
        
        //Cria um tópico no fórum de notícias sobre a atualização do calendário
        $forum = forum_get_course_forum($courseid, 'news'); //Procura o Id do fórum de notícias
        $discussion = new stdClass();
        $discussion->forum = $forum->id;
        $discussion->course = $courseid;
        $discussion->name = 'Atualização do Calendario'; //Título padrão do tópico
        if ($msgforum != ''){
            $discussion->message = $msgforum;
        } else {
            $discussion->message = 'O Calendario foi alterado'; //Mensagem padrão caso o usuário não mande
        }
        $discussion->messageformat = FORMAT_HTML;
        $discussion->messagetrust = 1;
        $discussion->mailnow = $flagemail; //Flag para indicar se deseja ou não enviar email aos alunos
        $discussion->attachments = null;
        $discussionid = forum_add_discussion($discussion);
        
        return 'Os eventos foram removidos com sucesso!!';
    }
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function insert_events_parameters() {
        return new external_function_parameters(
            array('calendar' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                            'courseid' => new external_value(PARAM_TEXT, 'Course Id'),
                                            'name' => new external_value(PARAM_TEXT, 'Event name'),
                                            'description' => new external_value(PARAM_TEXT, 'Event description'),
                                            'timestart' => new external_value(PARAM_TEXT, 'Event start date (YYYY;mm;dd;HH;ii)'),
                                            'timedurationuntil' => new external_value(PARAM_TEXT, 'Event end date (YYYY;mm;dd;HH;ii)'),
                                        )
                                    )
                                ),
                'msgforum' => new external_value(PARAM_TEXT, 'Forum message'),
                'flagemail' => new external_value(PARAM_BOOL, 'Send email'),
            )
        );
    }
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function update_events_parameters(){
        return new external_function_parameters(
            array(
                'calendar' => new external_multiple_structure(
                                    new external_single_structure(
                                            array(
                                                'courseid' => new external_value(PARAM_TEXT, 'Course Id'),
                                                'name' => new external_value(PARAM_TEXT, 'Event name'),
                                                'description' => new external_value(PARAM_TEXT, 'Event description'),
                                                'timestart' => new external_value(PARAM_TEXT, 'Event start date (YYYY;mm;dd;HH;ii)'),
                                                'timedurationuntil' => new external_value(PARAM_TEXT, 'Event end date (YYYY;mm;dd;HH;ii)'),
                                                'newname' => new external_value(PARAM_TEXT, 'Event new name, empty if dont change'),
                                                'newdescription' => new external_value(PARAM_TEXT, 'Event new description, empty if dont change'),
                                                'newtimestart' => new external_value(PARAM_TEXT, 'Event new start date, empty if dont change (YYYY;mm;dd;HH;ii)'),
                                                'newtimedurationuntil' => new external_value(PARAM_TEXT, 'Event new end date, empty if dont change (YYYY;mm;dd;HH;ii)'),
                                            )
                                    )
                                ),
                'msgforum' => new external_value(PARAM_TEXT, 'Forum message'),
                'flagemail' => new external_value(PARAM_BOOL, 'Send email'),
                )
        );
    }
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function remove_events_parameters(){
        return new external_function_parameters(
            array(
                'calendar' => new external_multiple_structure(
                                    new external_single_structure(
                                            array(
                                                'courseid' => new external_value(PARAM_TEXT, 'Course Id'),
                                                'name' => new external_value(PARAM_TEXT, 'Event name'),
                                                'description' => new external_value(PARAM_TEXT, 'Event description'),
                                                'timestart' => new external_value(PARAM_TEXT, 'Event start date (YYYY;mm;dd;HH;ii)'),
                                                'timedurationuntil' => new external_value(PARAM_TEXT, 'Event end date (YYYY;mm;dd;HH;ii)'),
                                            )
                                    )
                                ),
                'msgforum' => new external_value(PARAM_TEXT, 'Forum message'),
                'flagemail' => new external_value(PARAM_BOOL, 'Send email'),
                )
        );
    }
    
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function insert_events_returns() {
        return new external_value(PARAM_TEXT, 'Returns a sucess message');
    }
    
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function update_events_returns(){
        return new external_value(PARAM_TEXT, 'Returns a sucess message');
    }
    
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function remove_events_returns(){
        return new external_value(PARAM_TEXT, 'Returns a sucess message');
    }
}
