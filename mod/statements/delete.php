<?PHP // $Id: delete.php,v 1.2 2006/11/21 19:26:36 skodak Exp $


require('teacheraccess.php'); //page only for teachers
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$PAGE->set_url('/mod/statements/delete.php', ['id' => $id, 'chapterid' => $chapterid]);

///header and strings
$strstatementss = get_string('modulenameplural', 'statements');
$strstatements  = get_string('modulename', 'statements');

if ($course->category) {
    $navigation = '<a href="../../course/view.php?id='.$course->id.'">'.$course->shortname.'</a> ->';
} else {
    $navigation = '';
}

echo $OUTPUT->header();
echo $OUTPUT->heading("$course->shortname: $statements->name");
///form processing
if ($confirm) {  // the operation was confirmed.

    if (!$DB->Execute('DELETE FROM mdl_statements_problems_correlation WHERE mdl_statements_problems_correlation.statement_id = '.$statements->id.' AND mdl_statements_problems_correlation.problem_id = '.$chapter->id)) {
        error('Could not update your statements');
    }

	$chapters = $DB->get_records_sql('SELECT * FROM mdl_statements_problems_correlation WHERE statement_id = '.$statements->id.' ORDER BY rank');

	$i=0;
	foreach ($chapters as $ch) {
		$ch->rank=$i+1;
		$i++;
		if (!$DB->Execute('UPDATE mdl_statements_problems_correlation SET rank = '.$ch->rank.' WHERE mdl_statements_problems_correlation.statement_id = '.$statements->id.' AND mdl_statements_problems_correlation.problem_id = '.$ch->problem_id)) {
			echo 'UPDATE mdl_statements_problems_correlation SET rank = '.$ch->rank.' WHERE mdl_statements_problems_correlation.statement_id = '.$statements->id.' AND mdl_statements_problems_correlation.problem_id = '.$ch->problem_id;
			error('Could not update your statements');
		}
	}
	
	
    //add_to_log($course->id, 'course', 'update mod', '../mod/statements/view3.php?id='.$cm->id, 'statements '.$statements->id);
    //add_to_log($course->id, 'statements', 'update', 'view3.php?id='.$cm->id, $statements->id, $cm->id);
    statements_check_structure($statements->id);
}
redirect(new moodle_url('/mod/statements/view.php', ['id' => $cm->id]));
?>
