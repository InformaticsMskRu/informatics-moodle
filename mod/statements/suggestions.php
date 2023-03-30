<?PHP // $Id: view.php,v 1.2.8.2 2007/06/17 10:36:37 stronk7 Exp $
   

require_once('../../config.php');
require_once('lib.php');
$view         = required_param('view', PARAM_INT);           // Suggestion ID

global $url;

if ($view==0 || $view==1 || $view==-1) {
	$query='SELECT mdl_suggest.id, resolution, comment, lastname, firstname,  problem_id, field, new_value, user_id from mdl_suggest, mdl_user where resolution="'.$view.'" AND mdl_user.id=user_id ORDER BY mdl_suggest.id DESC';
}
if ($view==2) {
	$query='SELECT mdl_suggest.id, resolution, comment, lastname, firstname,  problem_id, field, new_value, user_id from mdl_suggest, mdl_user where mdl_user.id=user_id AND user_id='.$USER->id.' ORDER BY mdl_suggest.id DESC';
}
$suggestions = get_records_sql($query);

print_header( "Предложения",
              "Предложения",
              "Предложения"
		//."->".$last_nav_name
	      ,
              '',
              '<style type="text/css">@import url('.$CFG->wwwroot.'/mod/statements/statements_theme.css);</style>',
              true,
              "",
              /*navmenu($course, $cm)*/ null
            );

?>
<p><a href="/moodle/mod/statements/suggestions.php?view=2">Мои предложения</a>
:: <a href="/moodle/mod/statements/suggestions.php?view=-1">Все отклоненные</a>
:: <a href="/moodle/mod/statements/suggestions.php?view=0">Все нерассмотренные</a>
:: <a href="/moodle/mod/statements/suggestions.php?view=1">Все одобренные</a>
<p><table border="1">
<tr>
  <td>Задача</td>
  <td>Резолюция</td>
  <td>Предложен</td>
  <td>Автор</td>
  <td>Текст</td>
  <td>Исходная версия текста</td>
</tr>
<?php 
  foreach ($suggestions as $s) {
?>
<tr>
  <td><a href="view3.php?chapterid=<?php echo $s->problem_id; ?>"><?php echo $s->problem_id; ?></a></td>
  <td>
<?php
  if (isadmin() AND $s->resolution==0) { ?>
	<nobr><form method="post" action="suggestion_approve.php">
        	<input type="hidden" name="id" value="<?php echo $s->id; ?>">
		<input type="hidden" name="approve" value="1">
		<input type="hidden" name="chapterid" value="<?php echo $s->problem_id; ?>">
		<input type="hidden" name="analysis" value="<?php echo htmlspecialchars($s->new_value); ?>">
		<input type="hidden" name="name" value="<?php echo $s->firstname,' ',$s->lastname;?>">
		<input type="hidden" name="userid" value="<?php echo $s->user_id;?>">
		<input type="hidden" name="type" value="<?php echo $s->field; ?>">
		<input type="submit" value="Добавить">
	</form>
	<form method="post" action="suggestion_approve.php">
		<input type="text" name="comment" value="Пожалуйства, исправьте опечатки, грамматические и пунктуационные ошибки." size=30 width=1000>
        	<input type="hidden" name="id" value="<?php echo $s->id; ?>">
		<input type="hidden" name="approve" value="-1">
		<input type="submit" value="Отклонить">
	</form></nobr>	
<?php	} else {
	if ($s->resolution==1) { echo '<font color="green">Одобрено</font>'; }
	if($s->resolution==-1) { echo '<font color="red">Отклонено:</font> '.$s->comment; }
	if($s->resolution==0) { echo 'Рассматривается'; }
	} 	?>
  </td>
  <td><?php  echo print_string($s->field, "statements");?></td>
  <td><a href="/moodle/user/view.php?id=<?php echo $s->user_id; ?>"><?php echo $s->firstname,' ',$s->lastname;?></a></td>
  <td>
  <?php
  if($s->field == 'subject'){
  $get = mysql_query("SELECT * FROM ps_subjects WHERE subject_id = '$s->new_value' LIMIT 1");
  $getname = mysql_fetch_array($get);
  echo $getname['name'];
  }
  else {
  echo $s->new_value; 
  }
  
  ?></td>
  <td>
  <pre><?php echo htmlspecialchars($s->new_value); ?></pre>
  
  </td>
</tr>
<?php } ?>
</table>
<?php
print_footer();
  
?>