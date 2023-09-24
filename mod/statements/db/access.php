<?php
$capabilities = array(
	'mod/statements:viewall' => array(
		'riskbitmask'  => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG,
		'captype'      => 'write',
		'contextlevel' => CONTEXT_MODULE,
		'archetypes'   => array(
			'manager' => CAP_ALLOW,
		)
	),
	'mod/statements:authteacher' =>array(
		'riskbitmask'  => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG,
		'captype'	   => 'write',
		'contextlevel' => CONTEXT_MODULE,
		'archetypes'   => array(
			'ejudge_teacher' => CAP_ALLOW,
		)
	),
	'mod/statements:teacher' =>array(
		'riskbitmask'  => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG,
		'captype'	   => 'write',
		'contextlevel' => CONTEXT_MODULE,
		'archetypes'   => array(
			'ejudge_teacher' => CAP_ALLOW,
		)
	),
 	'mod/statement:view_source' =>array(
		'riskbitmask'  => RISK_PERSONAL | RISK_CONFIG,
		'captype'	   => 'read',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes'   => array(
			'manager' => CAP_ALLOW,
		)
	),
 	'mod/statement:view_protocol' =>array(
		'riskbitmask'  => RISK_PERSONAL | RISK_CONFIG,
		'captype'	   => 'read',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes'   => array(
			'manager' => CAP_ALLOW,
		)
	),
	'moodle/site:edit_problem' =>array(
		'riskbitmask'  => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG,
		'captype'	   => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes'   => array(
			'manager' => CAP_ALLOW,
		)
    ),
	'moodle/ejudge_submits:rejudge' =>array(
		'riskbitmask'  => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG,
		'captype'	   => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes'   => array(
			'manager' => CAP_ALLOW,
		)
    ),
    'moodle/ejudge_submits:admin' =>array(
		'riskbitmask'  => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG,
		'captype'	   => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes'   => array(
			'manager' => CAP_ALLOW,
		)
    ),
    'moodle/ejudge_group:admin' =>array(
		'riskbitmask'  => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG,
		'captype'	   => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes'   => array(
			'manager' => CAP_ALLOW,
		)
    ),
    'moodle/ejudge_monitor:admin' =>array(
		'riskbitmask'  => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG,
		'captype'	   => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes'   => array(
			'manager' => CAP_ALLOW,
		)
    ),

)
?>
