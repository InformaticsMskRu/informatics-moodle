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
if (($form = data_submitted()) && (confirm_sesskey()) && $_POST["subjects"]) {
      		
	if(!empty($_POST['subjects']['0'])){ $query1=$db->execute("INSERT into mdl_suggest SET user_id='$USER->id', problem_id = '.$_POST[chapterid].', field='subject', new_value = ".$_POST['subjects']['0'].""); }
	elseif(!empty($_POST['subjects']['1'])){ $query2=$db->execute("INSERT into mdl_suggest SET user_id='$USER->id', problem_id = '.$_POST[chapterid].', field='subject', new_value = ".$_POST['subjects']['1'].""); }
	elseif(!empty($_POST['subjects']['2'])){ $query3=$db->execute("INSERT into mdl_suggest SET user_id='$USER->id', problem_id = '.$_POST[chapterid].', field='subject', new_value = ".$_POST['subjects']['2'].""); }
	elseif(!empty($_POST['subjects']['3'])){ $query4=$db->execute("INSERT into mdl_suggest SET user_id='$USER->id', problem_id = '.$_POST[chapterid].', field='subject', new_value = ".$_POST['subjects']['3'].""); }
	if (!$query1 && !$query2 && !$query3 && !$query4) {
		error('Cant write to db');
	} else {
		
		error("Спасибо! Ваше предложение сохранено и темы будут добавлены в случае одобрения редактором. Вы можете следить за их судьбой <a href='suggestions.php?view=2'>здесь</a>.");	
	}
    die;
}
else
{
	error("Something wrong...");
}
?>
