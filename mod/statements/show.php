<?PHP // $Id: show.php,v 1.2 2006/11/21 19:26:36 skodak Exp $

require('teacheraccess.php'); //page only for teachers

$chapter->cur_hidden = $chapter->cur_hidden ? 0 : 1;

if (!$DB->Execute('UPDATE mdl_statements_problems_correlation SET hidden = '.$chapter->cur_hidden.' WHERE mdl_statements_problems_correlation.statement_id = '.$statements->id.' AND mdl_statements_problems_correlation.problem_id = '.$chapter->id)) {
    error('Could not update your statements');
}
add_to_log($course->id, 'course', 'update mod', '../mod/statements/view.php?id='.$cm->id, 'statements '.$statements->id);
add_to_log($course->id, 'statements', 'update', 'view3.php?id='.$cm->id, $statements->id, $cm->id);
statements_check_structure($statements->id);
redirect('view.php?id='.$cm->id.'&chapterid='.$chapter->id);
die;

?>
