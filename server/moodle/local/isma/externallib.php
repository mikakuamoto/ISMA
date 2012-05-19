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
        
        $cont = 0;
        
        //Adiciona todos os eventos no calendário
        for ($i = 0; $i < sizeof($calendar); $i++) {
            $temp = $calendar[$i];
            $newevent = new stdClass();
            $newevent->eventtype = 'course'; //Tipo do evento
            $newevent->courseid = $courseid;
            $newevent->name = $temp['name'];
            $newevent->description = $temp['description'];
            
            $timestarttemp = explode(' ', $temp['timestart']);
            $datestart = explode('/', $timestarttemp[0]);
            $hourstart = explode(':', $timestarttemp[1]);
            $newevent->timestart = make_timestamp((int) $datestart[2], (int) $datestart[1], (int) $datestart[0], (int) $hourstart[0], (int) $hourstart[1]);
            
            $timeendtemp = explode(';', $temp['timedurationuntil']);
            $dateend = explode('/', $timeendtemp[0]);
            $hourend = explode(':', $timeendtemp[1]);
            $newevent->timedurationuntil = make_timestamp((int) $dateend[2], (int) $dateend[1], (int) $dateend[0], (int) $hourend[0], (int) $hourend[1]);
            
            $newevent->timeduration = $newevent->timedurationuntil- $newevent->timestart; //Duração do evento
            $newevent = new calendar_event($newevent);
            $newevent->update($newevent);
            $cont++;
        }   
        
        //Se o parâmetro não estiver vazio, insere uma mensagem no fórum de notícias
        if($msgforum != ""){
            //Chama o método de inserir tópico no fórum
            $discussionid = local_isma_insert_msg_into_forum($courseid, $msgforum, $flagemail);
        }
        
        return 'Foram inseridos ' . $cont . ' evento(s) com sucesso!!';
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
        
        $cont = 0;
        
        //Atualiza os eventos
        for ($i = 0; $i < sizeof($calendar); $i++) {
            $temp = $calendar[$i];
            
            $timestarttemp = explode(' ', $temp['timestart']);
            $datestart = explode('/', $timestarttemp[0]);
            $hourstart = explode(':', $timestarttemp[1]);
            $timestart = make_timestamp((int) $datestart[2], (int) $datestart[1], (int) $datestart[0], (int) $hourstart[0], (int) $hourstart[1]);
            
            $timeendtemp = explode(';', $temp['timedurationuntil']);
            $dateend = explode('/', $timeendtemp[0]);
            $hourend = explode(':', $timeendtemp[1]);
            $timedurationuntil = make_timestamp((int) $dateend[2], (int) $dateend[1], (int) $dateend[0], (int) $hourend[0], (int) $hourend[1]);
            
            foreach ($events as $event){           
                //Procura o evento que seja do mesmo horário
                if($event->timestart == $timestart && $event->timeduration == $timedurationuntil - $timestart){
                    //Verifica se o nome ou a descrição está diferente
                    if($event->name != $temp['name'] || $event->description != $temp['description']){
                        $newevent = new stdClass();
                        $newevent->id = $event->id;
                        $newevent->eventtype = 'course'; //Tipo do evento
                        $newevent->courseid = $courseid;
                        $newevent->name = $temp['name'];
                        $newevent->description = $temp['description'];
                        $newevent->timestart = $timestart;
                        $newevent->timedurationuntil = $timedurationuntil;
                        $newevent->timeduration = $newevent->timedurationuntil- $newevent->timestart;
                        $newevent = new calendar_event($newevent);
                        $newevent->update($newevent);
                        $cont++;
                    }
                }
            }
        }
        
        //Se o parâmetro não estiver vazio, insere uma mensagem no fórum de notícias
        if($msgforum != ""){
            //Chama o método de inserir tópico no fórum
            $discussionid = local_isma_insert_msg_into_forum($courseid, $msgforum, $flagemail);
        }
        
        return 'Foram alterados ' . $cont . ' evento(s) com sucesso!!';
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
        
        $cont = 0;
        
        for ($i = 0; $i < sizeof($calendar); $i++) {
            $temp = $calendar[$i];
            
            $timestarttemp = explode(' ', $temp['timestart']);
            $datestart = explode('/', $timestarttemp[0]);
            $hourstart = explode(':', $timestarttemp[1]);
            $timestart = make_timestamp((int) $datestart[2], (int) $datestart[1], (int) $datestart[0], (int) $hourstart[0], (int) $hourstart[1]);
            
            $timeendtemp = explode(';', $temp['timedurationuntil']);
            $dateend = explode('/', $timeendtemp[0]);
            $hourend = explode(':', $timeendtemp[1]);
            $timedurationuntil = make_timestamp((int) $dateend[2], (int) $dateend[1], (int) $dateend[0], (int) $hourend[0], (int) $hourend[1]);
            
            //Compara os eventos passados por parâmetro com os que já estão no calendário pela data do
            //evento e pelo nome, se for igual, remove.
            foreach ($events as $event){
                if($event->timestart == $timestart && $event->timeduration == $timedurationuntil - $timestart && $event->name == $temp['name']) {                    
                    $event = new calendar_event($event);
                    $event->delete(false);
                    $cont++;
                }
            }
        }    
        
        //Se o parâmetro não estiver vazio, insere uma mensagem no fórum de notícias
        if($msgforum != ""){
            //Chama o método de inserir tópico no fórum
            $discussionid = local_isma_insert_msg_into_forum($courseid, $msgforum, $flagemail);
        }
        
        return 'Foram removidos ' . $cont . ' evento(s) com sucesso!!';
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
                                                'timestart' => new external_value(PARAM_TEXT, 'Data e hora início do evento (dd/mm/YYYY HH:ii)'),
                                                'timedurationuntil' => new external_value(PARAM_TEXT, 'Data e hora final do evento (dd/mm/YYYY HH:ii)'),
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
                                                'timestart' => new external_value(PARAM_TEXT, 'Data e hora início do evento (dd/mm/YYYY HH:ii)'),
                                                'timedurationuntil' => new external_value(PARAM_TEXT, 'Data e hora final do evento (dd/mm/YYYY HH:ii)'),
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
                                                'timestart' => new external_value(PARAM_TEXT, 'Data e hora início do evento (dd/mm/YYYY HH:ii)'),
                                                'timedurationuntil' => new external_value(PARAM_TEXT, 'Data e hora final do evento (dd/mm/YYYY HH:ii)'),
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
