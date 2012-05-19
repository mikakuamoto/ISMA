<?php

/**
 * Plugin local web service ISMA  
 * Lib
 *
 * @package	localwsisma
 * @author	Mika Kuamoto - Paulo Silveira  
 * 
 */
require_once($CFG->dirroot . '/mod/forum/lib.php');

/**
 * Procura todos os eventos de um curso
 * @param $courseid
 * @return lista com todos os eventos 
 */
function local_isma_get_events_by_course($courseid) {
    global $DB;

    $whereclause = 'courseid = '.$courseid;

    $events = $DB->get_records_select('event', $whereclause, null, 'timestart');
    if ($events === false) {
        $events = array();
    }
    return $events;
}

/**
 * Insere um tópico no fórum de notícias
 * @param $courseid
 * @param $msgforum
 * @param $flagemail
 * @return Id do tópico 
 */
function local_isma_insert_msg_into_forum($courseid, $msgforum, $flagemail){  

    $forum = forum_get_course_forum($courseid, 'news'); //Procura o Id do fórum de notícias

    $discussion = new stdClass();
    $discussion->forum = $forum->id;
    $discussion->course = $courseid;
    $discussion->name = 'Atualização dos Eventos'; //Título padrão do tópico
    $discussion->message = $msgforum;
    $discussion->messageformat = FORMAT_HTML;
    $discussion->messagetrust = 1;
    $discussion->mailnow = $flagemail; //Flag para indicar se deseja ou não enviar email aos alunos
    $discussion->attachments = null;
    return forum_add_discussion($discussion);  
}
