<?PHP // $Id: view.php,v 1.2.8.2 2007/06/17 10:36:37 stronk7 Exp $
  
global $url;

require_once('../../config.php');
require_once('lib.php');
require_once('../../../inf/include/stuff.php');

$id         = required_param('id', PARAM_INT);           // Suggestion ID
$approve  = required_param('approve', PARAM_INT); // 1 - approve, -1 - reject
$chapterid  = optional_param('chapterid', PARAM_INT); 
$analysis  = optional_param('analysis', PARAM_INT); 
$name  = optional_param('name', PARAM_INT); 
$userid  = optional_param('userid', PARAM_INT); 
$comment = optional_param('comment', PARAM_INT); 
if(isadmin()) {
	$query = 'UPDATE mdl_suggest set resolution = '.$approve.', resolution_user_id = '.$USER->id.', comment =" '.$comment.'" WHERE id = '. $id; 
	if(!$db->Execute($query)) {
		error("Can't update database (mdl_suggest table).");
	}
} else {
  error("Only administrator can approve suggestions.");
}
if($approve==1) {
	if($_POST['type'] == 'subject'){
	            $update = mysql_query("UPDATE mdl_user SET helprate=helprate+1 WHERE id = $userid LIMIT 1"); 
				create_new_subject_moodle($chapterid,$analysis);
	}
   	else {
		$anres = get_record_sql("SELECT analysis FROM mdl_problems WHERE id = ". $chapterid);
		if($anres -> analysis == "") {
			$analysis = "\""."<p align='right'><i>Разбор добавил <a href='".$CFG->wwwroot."/user/view.php?id=".$userid."'>".$name."</a></i></p>".$analysis."\"";
			$query = 'UPDATE mdl_problems set analysis = '.$analysis.' WHERE id = '. $chapterid; 
			$update = mysql_query("UPDATE mdl_user SET helprate=helprate+5 WHERE id = $userid LIMIT 1"); 
			create_new_subject_moodle($chapterid,$analysis);
			if(!$db->Execute($query)) {
				error("Can't update database.");
			}
		} 
		else 
		{
			$analysis = "\"".$analysis."<p align='right'><i>Отредактировал(а) <a href='".$CFG->wwwroot."/user/view.php?id=".$userid."'>".$name."</a></i></p>"."\"";
			$query = 'UPDATE mdl_problems set analysis = '.$analysis.' WHERE id = '. $chapterid; 
			$update = mysql_query("UPDATE mdl_user SET helprate=helprate+2 WHERE id = $userid LIMIT 1"); 
			#create_new_subject_moodle($chapterid,$analysis);
			if(!$db->Execute($query)) {
				error("Can't update database.");
			}
		}
	} 	
}

redirect("suggestions.php?view=0");	
?>
