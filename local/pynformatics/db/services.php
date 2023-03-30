<?php

$functions = array(
	'local_pynformatics_has_capability' => array(
		'classname'   => 'local_pynformatics_external',
		'methodname'  => 'has_capability',
		'classpath'   => 'local/pynformatics/externallib.php',
		'description' => 'Check capability for pynformatics',
		'type'        => 'read',
	)
);

$services = array(
	'pynformatics' => array(
		'functions' => array ('local_pynformatics_has_capability'),
		'enabled'=>1,
		'restrictedusers' => 1,
	)
)

?>
