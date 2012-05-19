<?php

/**
 * Bloco My Events ISMA  
 * Form para editar visualização dos eventos
 *
 * @package	moodlecore
 * @author	Mika Kuamoto - Paulo Silveira
 */

class block_my_events_isma_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $options = array(
            'all' =>get_string('allevents', 'block_my_events_isma'), //Mostrar todos os eventos
            'important' =>get_string('importantevents', 'block_my_events_isma') //Mostrar apenas eventos importantes
            );

        $mform->addElement('select', 'config_eventview', get_string('showevents', 'block_my_events_isma'), $options);
        $mform->setDefault('config_eventview', 'all');
    }
}


