<?php

/**
 * Plugin local web service ISMA
 * Implementação das funções externas
 *
 * @package	localwsisma
 * @author	Mika Kuamoto - Paulo Silveira 
 */

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->dirroot . '/local/isma/lib.php');

class local_isma_external extends external_api {
    
    /**
     * Adiciona eventos de um curso ao seu calendário
     * @param array $calendar
     * @param int $courseid
     * @param string $msgforum
     * @param boolean $flagemail
     * @return string retorna uma mensagem de sucesso
     */
    public static function insert_events($calendar, $courseid, $msgforum, $flagemail) {
        global $USER;

        //Validação dos parâmetros
        $params = self::validate_parameters(self::insert_events_parameters(), 
                    array('calendar' => $calendar, 'courseid' => $courseid, 
                            'msgforum' => $msgforum, 'flagemail' => $flagemail));

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
            $newevent->courseid = $courseid;
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
        
        //Se o parâmetro não estiver vazio, insere uma mensagem no fórum de notícias
        if($msgforum != ""){
            //Chama o método de inserir tópico no fórum
            $discussionid = local_isma_insert_msg_into_forum($courseid, $msgforum, $flagemail);
        }
        
        return 'Os eventos foram inseridos com sucesso!!';
    }
    
    /**
     * Atualiza eventos do calendário de um curso 
     * @param array $calendar
     * @param int $courseid
     * @param string $msgforum
     * @param boolean $flagemail
     * @return string retorna uma mensagem de sucesso
     */
    public static function update_events($calendar, $courseid, $msgforum, $flagemail){
        global $USER;

        //Validação dos parâmetros
        $params = self::validate_parameters(self::update_events_parameters(), 
                    array('calendar' => $calendar, 'courseid' => $courseid, 
                            'msgforum' => $msgforum, 'flagemail' => $flagemail));

        //Validação do contexto
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Validação da capacidade
        if (!has_capability('moodle/calendar:manageentries', $context)) {
            throw new moodle_exception('nopermissiontoupdatecalendar');
        }
        
        //Pega todos os eventos do calendário do curso     
        $events = array();
        $events = local_isma_get_events_by_course($courseid);
        
        //Atualiza os eventos
        for ($i = 0; $i < sizeof($calendar); $i++) {
            $temp = $calendar[$i];
            $newevent = new stdClass();
            $newevent->eventtype = 'course'; //Tipo do evento
            $newevent->courseid = $courseid;
            $newevent->name = $temp['name'];
            $newevent->description = $temp['description'];
            
            $timestarttemp = explode(';', $temp['timestart']);
            $timestart = make_timestamp((int) $timestarttemp[0], (int) $timestarttemp[1], (int) $timestarttemp[2], (int) $timestarttemp[3], (int) $timestarttemp[4]);
            $newevent->timestart = $timestart;
            
            $timeendtemp = explode(';', $temp['timedurationuntil']);
            $timedurationuntil = make_timestamp((int) $timeendtemp[0], (int) $timeendtemp[1], (int) $timeendtemp[2], (int) $timeendtemp[3], (int) $timeendtemp[4]);
            $newevent->timedurationuntil = $timedurationuntil;
            
            $newevent->timeduration = $newevent->timedurationuntil- $newevent->timestart;

            //Compara os eventos passados por parâmetro com os que já estão no calendário pela data do
            //evento se for igual, pega o id e coloca no novo evento para atualizar.
            foreach ($events as $event){           
                if($event->timestart == $timestart && $event->timeduration == $timedurationuntil - $timestart){
                    $newevent->id = $event->id;
                    $newevent = new calendar_event($newevent);
                    $newevent->update($newevent);
                }
            }
        }
        
        //Se o parâmetro não estiver vazio, insere uma mensagem no fórum de notícias
        if($msgforum != ""){
            //Chama o método de inserir tópico no fórum
            $discussionid = local_isma_insert_msg_into_forum($courseid, $msgforum, $flagemail);
        }
        
        return 'Os eventos foram atualizados com sucesso!!';
    }
    
    /**
     * Remove eventos de um curso do seu calendário
     * @param array $calendar
     * @param int $courseid
     * @param string $msgforum
     * @param boolean $flagemail
     * @return string retorna uma mensagem de sucesso
     */
    public static function remove_events($calendar, $courseid, $msgforum, $flagemail){
        global $USER;

        //Validação dos parâmetros
        $params = self::validate_parameters(self::remove_events_parameters(), 
                    array('calendar' => $calendar, 'courseid' => $courseid, 
                            'msgforum' => $msgforum, 'flagemail' => $flagemail));

        //Validação do contexto
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Validação da capacidade
        if (!has_capability('moodle/calendar:manageentries', $context)) {
            throw new moodle_exception('nopermissiontoupdatecalendar');
        }
        
        //Pega todos os eventos do calendário do curso
        $events = array();
        $events = local_isma_get_events_by_course($courseid);
        
        for ($i = 0; $i < sizeof($calendar); $i++) {
            $temp = $calendar[$i];
            $timestarttemp = explode(';', $temp['timestart']);
            $timestart = make_timestamp((int) $timestarttemp[0], (int) $timestarttemp[1], (int) $timestarttemp[2], (int) $timestarttemp[3], (int) $timestarttemp[4]);
            $timeendtemp = explode(';', $temp['timedurationuntil']);
            $timedurationuntil = make_timestamp((int) $timeendtemp[0], (int) $timeendtemp[1], (int) $timeendtemp[2], (int) $timeendtemp[3], (int) $timeendtemp[4]);
           
            //Compara os eventos passados por parâmetro com os que já estão no calendário pela data do
            //evento e pelo nome, se for igual, remove.
            foreach ($events as $event){
                if($event->timestart == $timestart && $event->timeduration == $timedurationuntil - $timestart && $event->name == $temp['name']) {                    
                    $event = new calendar_event($event);
                    $event->delete(false);
                }
            }
        }    
        
        //Se o parâmetro não estiver vazio, insere uma mensagem no fórum de notícias
        if($msgforum != ""){
            //Chama o método de inserir tópico no fórum
            $discussionid = local_isma_insert_msg_into_forum($courseid, $msgforum, $flagemail);
        }
        
        return 'Os eventos foram removidos com sucesso!!';
    }
    
    /**
     * Retorna a descrição dos parâmetros do método
     * @return external_function_parameters
     */
    public static function insert_events_parameters() {
        return new external_function_parameters(
            array(
                'calendar' => new external_multiple_structure(
                                    new external_single_structure(
                                            array(
                                                'name' => new external_value(PARAM_TEXT, 'Nome do evento'),
                                                'description' => new external_value(PARAM_TEXT, 'Descrição do evento'),
                                                'timestart' => new external_value(PARAM_TEXT, 'Data e hora início do evento (YYYY;mm;dd;HH;ii)'),
                                                'timedurationuntil' => new external_value(PARAM_TEXT, 'Data e hora final do evento (YYYY;mm;dd;HH;ii)'),
                                            )
                                    )
                                ),
                'courseid' => new external_value(PARAM_INT, 'Identificador do curso'),
                'msgforum' => new external_value(PARAM_TEXT, 'Mensagem do fórum'),
                'flagemail' => new external_value(PARAM_BOOL, 'Enviar email'),
                )
        );
    }
    
    /**
     * Retorna a descrição dos parâmetros do método
     * @return external_function_parameters
     */
    public static function update_events_parameters(){
        return new external_function_parameters(
            array(
                'calendar' => new external_multiple_structure(
                                    new external_single_structure(
                                            array(
                                                'name' => new external_value(PARAM_TEXT, 'Nome do evento'),
                                                'description' => new external_value(PARAM_TEXT, 'Descrição do evento'),
                                                'timestart' => new external_value(PARAM_TEXT, 'Data e hora início do evento (YYYY;mm;dd;HH;ii)'),
                                                'timedurationuntil' => new external_value(PARAM_TEXT, 'Data e hora final do evento (YYYY;mm;dd;HH;ii)'),
                                            )
                                    )
                                ),
                'courseid' => new external_value(PARAM_INT, 'Identificador do curso'),
                'msgforum' => new external_value(PARAM_TEXT, 'Mensagem do fórum'),
                'flagemail' => new external_value(PARAM_BOOL, 'Enviar email'),
                )
        );
    }
    
    /**
     * Retorna a descrição dos parâmetros do método
     * @return external_function_parameters
     */
    public static function remove_events_parameters(){
        return new external_function_parameters(
            array(
                'calendar' => new external_multiple_structure(
                                    new external_single_structure(
                                            array(
                                                'name' => new external_value(PARAM_TEXT, 'Nome do evento'),
                                                'description' => new external_value(PARAM_TEXT, 'Descrição do evento'),
                                                'timestart' => new external_value(PARAM_TEXT, 'Data e hora início do evento (YYYY;mm;dd;HH;ii)'),
                                                'timedurationuntil' => new external_value(PARAM_TEXT, 'Data e hora final do evento (YYYY;mm;dd;HH;ii)'),
                                            )
                                    )
                                ),
                'courseid' => new external_value(PARAM_INT, 'Identificador do curso'),
                'msgforum' => new external_value(PARAM_TEXT, 'Mensagem do fórum'),
                'flagemail' => new external_value(PARAM_BOOL, 'Enviar email'),
                )
        );
    }
    
    /**
     * Retorna a descrição do resultado do método
     * @return external_description
     */
    public static function insert_events_returns() {
        return new external_value(PARAM_TEXT, 'Retorna uma mensagem de sucesso');
    }
    
    /**
     * Retorna a descrição do resultado do método
     * @return external_description
     */
    public static function update_events_returns(){
        return new external_value(PARAM_TEXT, 'Retorna uma mensagem de sucesso');
    }
    
    /**
     * Retorna a descrição do resultado do método
     * @return external_description
     */
    public static function remove_events_returns(){
        return new external_value(PARAM_TEXT, 'Retorna uma mensagem de sucesso');
    }
}
