<?php

/**
 * Plugin local web service ISMA  
 * Definições das funções externas e do serviço
 *
 * @package	localwsisma
 * @author	Mika Kuamoto - Paulo Silveira
 * 
 */

// Funções do web service
$functions = array(
        'local_isma_insert_events' => array(
                'classname'   => 'local_isma_external',
                'methodname'  => 'insert_events',
                'classpath'   => 'local/isma/externallib.php',
                'description' => 'Adiciona eventos de um curso ao seu calendário',
                'capabilities'=> 'moodle/calendar:manageentries',
                'type'        => 'write',
        ),
        'local_isma_update_events' => array(
                'classname'   => 'local_isma_external',
                'methodname'  => 'update_events',
                'classpath'   => 'local/isma/externallib.php',
                'description' => 'Atualiza eventos do calendário de um curso',
                'capabilities'=> 'moodle/calendar:manageentries',
                'type'        => 'write',
        ),
        'local_isma_remove_events' => array(
                'classname'   => 'local_isma_external',
                'methodname'  => 'remove_events',
                'classpath'   => 'local/isma/externallib.php',
                'description' => 'Remove eventos de um curso do seu calendário',
                'capabilities'=> 'moodle/calendar:manageentries',
                'type'        => 'write',
        ),
);

// Serviços
$services = array(
        'ISMA' => array(
                'functions' => array (
                        'local_isma_insert_events',
                        'local_isma_update_events',
                        'local_isma_remove_events'),
                'restrictedusers' => 0,
                'enabled'=>1,
        ),
);
