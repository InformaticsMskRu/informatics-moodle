<?php     
	$subjects = get_records_sql('SELECT s.subject_id,name from ps_problem_subjects as p, ps_subjects as s WHERE leaf=1 AND p.problem_id='.$chapterid.' AND p.subject_id=s.subject_id ORDER BY p.order_value');
	if (!$chapter = get_record_sql('SELECT 
		*
	FROM
		mdl_problems
	WHERE 
		id = '.$chapterid)) {
		error('Error reading statements chapters.'.$chapterid);
	}

unset($chapterid);


/// If data submitted, then process and store.
if (($form = data_submitted()) && (confirm_sesskey())) {
    if ($chapter) {
        /// editing existing chapter
        $chapter->content = $form->content;
        $chapter->description = $form->description;
        $chapter->analysis = $form->analysis;
        $chapter->name = $form->name;
		///add slashes to all text fields
	$chapter->content = "\"".$chapter->content."\"";
	$chapter->description = "\"".$chapter->description."\"";
	$chapter->analysis = "\"".$chapter->analysis."\"";
	$chapter->name = "\"".$chapter->name."\"";
	if (!$db->Execute('UPDATE mdl_problems SET name = '.$chapter->name.', content = '.$chapter->content.', description = '.$chapter->description.', analysis = '.$chapter->analysis.' WHERE mdl_problems.id = '.$chapter->id)) {
		error('Could not update your statements');
	} 		
	//SUBJECTS UPDATE
	if (!$db->Execute('DELETE from ps_problem_subjects WHERE problem_id = '.$chapter->id)) {
		error('Could not delete your subjects');
	}       
	foreach ($_POST["subjects"] as $ids) {
		if ($ids>0) {
			create_new_subject_moodle($chapter->id,$ids);
		}
	}
    } 
    redirect("view3.php?chapterid=$chapter->id");
    die;
}

$usehtmleditor = can_use_html_editor();
print_header( "$chapter->name",
              $chapter->name,
              "$chapter->name",
              '',
              '',
              true,
              '',
              ''
            );

include('edit_problem.html');

if ($usehtmleditor ) {
    use_html_editor();
}
print_footer();
?>
