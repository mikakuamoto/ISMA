<?php

/**
 * Web service local plugin isma external functions and service definitions.
 *
 * @package		localwsisma
 * @author		Mika Kuamoto - Paulo Silveira  
 * 
 */

// Web service functions to install.
$functions = array(
        'local_isma_insert_events' => array(
                'classname'   => 'local_isma_external',
                'methodname'  => 'insert_events',
                'classpath'   => 'local/isma/externallib.php',
                'description' => 'Add events from one course to his calendar',
                'type'        => 'read',
        )
);

// Services to install as pre-build services.
$services = array(
        'ISMA' => array(
                'functions' => array ('local_isma_insert_events'),
                'restrictedusers' => 0,
                'enabled'=>1,
        )
);
