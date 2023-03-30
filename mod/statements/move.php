<?PHP // $Id: move.php,v 1.2 2006/11/21 19:26:36 skodak Exp $

require('teacheraccess.php'); //page only for teachers
$up  = optional_param('up', 0, PARAM_BOOL);


$chapters = $DB->get_records_sql('SELECT * FROM mdl_statements_problems_correlation WHERE statement_id = '.$statements->id.' ORDER BY rank');

$i=0;
$nchapters = array();
foreach ($chapters as $ch) {
	$nchapters[$i]=$ch;
	$i++;
}
$i=0;
foreach ($nchapters as $ch) {
    if ($chapter->id == $ch->problem_id) {
		if ($up && $i>0) {
			$nchapters[$i]->rank--;
			$nchapters[$i-1]->rank++;
		}		
		if (!$up && $i<sizeof($nchapters)-1) {
			$nchapters[$i]->rank++;
			$nchapters[$i+1]->rank--;
		}		
		break;
    }
	$i++;
}

//var_dump($chapters);

foreach ($nchapters as $ch) {
	//var_dump($ch);
    if (!$DB->Execute('UPDATE mdl_statements_problems_correlation SET rank = '.$ch->rank.' WHERE mdl_statements_problems_correlation.statement_id = '.$statements->id.' AND mdl_statements_problems_correlation.problem_id = '.$ch->problem_id)) {
		echo 'UPDATE mdl_statements_problems_correlation SET rank = '.$ch->rank.' WHERE mdl_statements_problems_correlation.statement_id = '.$statements->id.' AND mdl_statements_problems_correlation.problem_id = '.$ch->problem_id;
		error('Could not update your statements');
	}
}


//add_to_log($course->id, 'course', 'update mod', '../mod/statements/view3.php?id='.$cm->id, 'statements '.$statements->id);
//add_to_log($course->id, 'statements', 'update', 'view3.php?id='.$cm->id, $statements->id, $cm->id);
statements_check_structure($statements->id);
redirect('view.php?id='.$cm->id.'&chapterid='.$chapter->id);
die;

?>
