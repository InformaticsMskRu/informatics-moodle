<?php

global $problem_id;
global $contest_id;


require_once('../../config.php');
require_once('lib.php');

$cmid         = optional_param('cmid', 0, PARAM_INT);           // Course Module ID
$chapterid  = required_param('chapterid', PARAM_INT); // Chapter ID
$rank    = optional_param('rank', 0, PARAM_INT);

if ($cmid==0) {
	$context = context_system::instance();
        require_capability('moodle/site:edit_problem', $context);
} else {

// =========================================================================
// security checks START - only teachers edit
// =========================================================================

	$cm = get_coursemodule_from_id('statements', $cmid, 0, false, MUST_EXIST);
	$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
	$statements = $DB->get_record('statements', array('id'=>$cm->instance), '*', MUST_EXIST);

	require_login($course, false, $cm);

	$context = context_module::instance($cm->id);
#require_capability('moodle/course:manageactivities', $context);
	require_capability('moodle/site:edit_problem', $context);


$PAGE->set_url('/mod/statements/edit.php', array('cmid'=>$cmid, 'id'=>$chapterid, 'pagenum'=>$pagenum));

$select = "";
$chapters = $DB->get_records_sql('SELECT 
		mdl_problems.id, 
		mdl_statements_problems_correlation.rank,
		mdl_problems.name,
		mdl_statements_problems_correlation.hidden as cur_hidden,
		mdl_problems.pr_id,
		mdl_ejudge_problem.contest_id
	FROM
		mdl_problems, mdl_statements_problems_correlation, mdl_ejudge_problem
	WHERE '.$select.' 
		mdl_ejudge_problem.id = mdl_problems.pr_id AND
		mdl_statements_problems_correlation.problem_id = mdl_problems.id AND
		mdl_statements_problems_correlation.statement_id = '.$statements->id.' 
	ORDER BY
		mdl_statements_problems_correlation.rank');

if (!$chapters)
{
	echo "There is not chapter here";
}
}
if ($chapterid>0 && isset($statements))
{
	if (!$chapter = $DB->get_record_sql('SELECT 
		mdl_problems.*,
		mdl_statements_problems_correlation.statement_id
	FROM
		mdl_problems, mdl_statements_problems_correlation
	WHERE 
		mdl_problems.id = mdl_statements_problems_correlation.problem_id AND
		mdl_statements_problems_correlation
.statement_id = '.$statements->id.' AND
		mdl_problems.id = '.$chapterid)) {
		error('Error reading statements chapters.'.$chapterid.$statements->id);
	}
} else if ($chapterid>0){
 	if (!$chapter = $DB->get_record_sql('SELECT 
		mdl_problems.*
	FROM
		mdl_problems
	WHERE 
		mdl_problems.id = '.$chapterid)) {
		error('Error reading statements chapters.'.$chapterid);
	}    
}

//check all variables
unset($id);
unset($chapterid);

// =========================================================================
// security checks END
// =========================================================================


/// If data submitted, then process and store.
if (($form = data_submitted()) && (confirm_sesskey())) {
    //TODO: skip it for now
    //prepare data - security checks
    //$form->title = clean_text($form->title, FORMAT_HTML);
    //$form->content = clean_text($form->content, FORMAT_HTML);
//        var_dump($chapter);
//        die;	
 
    if ($chapter) {
        /// editing existing chapter
        $chapter->content = $form->content["text"];
        $chapter->description = $form->description["text"];
        $chapter->analysis = $form->analysis["text"];
        $chapter->name = $form->name;
		$chapter->contest_id = $form->contest_id;
		$chapter->problem_id = $form->select_problem;
		///add slashes to all text fields
	//        var_dump($chapter);
        //die;	
 	if (!$DB->execute('UPDATE mdl_problems SET name = ?, content = ?, description = ?, analysis = ? WHERE mdl_problems.id = ?', array($chapter->name, $chapter->content, $chapter->description, $chapter->analysis, $chapter->id))) {
			error('Could not update your statements');
		}
        //add_to_log($course->id, 'course', 'update mod', '../mod/statements/view.php?id='.$cm->id, 'statements '.$statements->id);
        //add_to_log($course->id, 'statements', 'update', 'view3.php?id='.$cm->id.'&chapterid='.$chapter->id, $statements->id, $cm->id);
    } else {
        /// adding new chapter
        $chapter->statementsid = $statements->id;
        $chapter->rank = $form->rank; //place after given pagenum, lets hope it is a number
        $chapter->name = $form->name;
        $chapter->content = $form->content;
        $chapter->description = $form->description;
        $chapter->analysis = $form->analysis;
		$chapter->problem_id = (int)$form->select_problem;
        $chapter->hidden = 0;
		
		$chapter->content = "\"".mysql_escape_string(addslashes($chapter->content))."\"";
		$chapter->description = "\"".mysql_escape_string(addslashes($chapter->description))."\"";
		$chapter->analysis = "\"".mysql_escape_string(addslashes($chapter->analysis))."\"";
		$chapter->name = "\"".mysql_escape_string(addslashes($chapter->name))."\"";


		
		// ��������� ���� �� ��������� ������(  ������������ ����������� ������������ ����� � ejudge � ����� � moodle )
		$tmp = $DB->get_records_sql("SELECT id FROM mdl_problems WHERE pr_id = ".$chapter->problem_id);
		
		if ($tmp)
		{
			error("Problem already exists");
		}

		// ��������� ��������� ��� ��������� �����
        foreach($chapters as $ch) {
            if ($ch->rank >= $chapter->rank) {
                $ch->rank++;
				if (!$DB->Execute('UPDATE mdl_statements_problems_correlation SET rank = '.$ch->rank.' WHERE mdl_statements_problems_correlation.statement_id = '.$statements->id.' AND mdl_statements_problems_correlation.problem_id = '.$ch->id)) {
					error('Could not update rank in statements');
				}
            }
        }
		
		// ��������� ����� ������.
        if (!$DB->Execute('INSERT INTO mdl_problems(name,content,description,analysis,pr_id) VALUES ('.$chapter->name.','.$chapter->content.','.$chapter->description.','.$chapter->analysis.','.$chapter->problem_id.')')) {
            error('Could not insert a new chapter');
        }

		$chapter->id = $DB->Insert_ID();

		// ��������� ������������ statement � ������ .
        if (!$DB->Execute('INSERT INTO mdl_statements_problems_correlation(statement_id,problem_id,rank) VALUES ('.$statements->id.','.$chapter->id.','.$chapter->rank.')')) {
            error('Could not insert a new chapter_statement_correlation');
        }			
		
        add_to_log($course->id, 'course', 'update mod', '../mod/statements/view3.php?id='.$cm->id, 'statements '.$statements->id);
        add_to_log($course->id, 'statements', 'update', 'view3.php?id='.$cm->id.'&chapterid='.$chapter->id, $statements->id, $cm->id);
    }

    statements_check_structure($statements->id);
    redirect("view.php?id=$cm->id&chapterid=$chapter->id");
}

/// Otherwise fill and print the form.
$strstatements = get_string('modulename', 'statements');
$strstatementss = get_string('modulenameplural', 'statements');
$stredit = get_string('edit');
$pageheading = get_string('editingchapter', 'statements');

$usehtmleditor = 0;
//UNCOMMENT THIS TO ENABLE WYSIWYG EDITOR 
//$usehtmleditor = can_use_html_editor() && isadmin();

if (!$chapter) {
    $chapter->id = -1;
    $chapter->name = '';
    $chapter->content = '';
    $chapter->description = '';
    $chapter->analysis = '';
    $chapter->rank = $rank;
}

///prepare the page header
if ($course->category) {
    $navigation = '<a href="../../course/view.php?id='.$course->id.'">'.$course->shortname.'</a> ->';
} else {
    $navigation = '';
}

$PAGE->set_title($statements->name);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($statements->name));

$contests = $DB->get_records_sql("SELECT * FROM mdl_ejudge_contest");

foreach ($contests as $contest) {
	$problems = $DB->get_records_sql("SELECT * FROM mdl_ejudge_problem WHERE contest_id=".$contest->id);
	$contest->problems = $problems;
}

#$ret = $DB->get_record_sql("SELECT mdl_ejudge_problem.problem_id,mdl_ejudge_problem.contest_id FROM mdl_problems,mdl_ejudge_problem WHERE mdl_ejudge_problem.id = ".$chapter->pr_id);

#$chapter->contest_id = $ret->contest_id;
#$chapter->problem_id = $chapter->pr_id;

include('edit.html');

echo $OUTPUT->footer();
?>
