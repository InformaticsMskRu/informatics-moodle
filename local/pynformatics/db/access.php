<?php

$local_pynformatics_capabilities = array(
	'local/pynformatics:check_capability' => array(
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,
	),
	'local/pynformatics:problem_edit' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
	),
	'local/pynformatics:contest_reload' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
	),

	'local/pynformatics:problem_teacher_view' => array(
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,

	),
	'local/pynformatics:show_hidden_submits' => array(
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,
	),
	'moodle/ejudge_submits:comment' => array(
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,
	),
	'moodle/source_tree:manage_contest' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
	),
);

?>
