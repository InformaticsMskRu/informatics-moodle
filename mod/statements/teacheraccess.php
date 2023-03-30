<?PHP // $Id: teacheraccess.php,v 1.1.8.1 2007/05/20 06:01:59 skodak Exp $

///standard routine to allow only teachers in
///check of $id and $chapterid parameters

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

$id        = required_param('id', PARAM_INT);        // Course Module ID
$chapterid = required_param('chapterid', PARAM_INT); // Chapter ID

$cm = get_coursemodule_from_id('statements', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$statements = $DB->get_record('statements', ['id' => $cm->instance], '*', MUST_EXIST);
require_login();

if (!confirm_sesskey()) {
    error(get_string('confirmsesskeybad', 'error')); 
}

$context = context_module::instance($cm->id);
require_capability('moodle/course:manageactivities', $context);

if (!$chapter = $DB->get_record_sql('SELECT 
		mdl_problems.*,
		mdl_statements_problems_correlation.statement_id, 
		mdl_statements_problems_correlation.hidden cur_hidden
	FROM
		mdl_problems, mdl_statements_problems_correlation
	WHERE 
		mdl_problems.id = mdl_statements_problems_correlation.problem_id AND
		mdl_statements_problems_correlation
.statement_id = '.$statements->id.' AND
		mdl_problems.id = '.$chapterid)) {
		error('Incorrect chapter ID');
}

//check all variables
unset($id);
unset($chapterid);

?>
