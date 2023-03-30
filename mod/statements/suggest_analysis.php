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
if (($form = data_submitted()) && (confirm_sesskey()) && $_POST["analysis"]) {
        if($_POST['format'] == 1) { $analysis1 = $form->analysis; }
        elseif($_POST['format'] == 2) { $analysis12 = "<pre>".htmlspecialchars($form->analysis)."</pre>"; $analysis1 = "$analysis12"; }
      		
	$query="INSERT into mdl_suggest SET user_id='$USER->id', problem_id = '.$_POST[chapterid].', field='analysis', new_value = '$analysis1'";
	if (!$db->execute($query)) {
		error('Could not insert new suggest');
	} else {
		
		error ("Спасибо! Ваш разбор сохранен и будет опубликован в случае одобрения редактором. Вы можете следить за его судьбой <a href='suggestions.php?view=2'>здесь</a>.");	
	}
    die;
}
else
{
	error("Something wrong...");
}
?>
