<?PHP // $Id: view.php,v 1.2.8.2 2007/06/17 10:36:37 stronk7 Exp $

// Версия для работы с отдельной базой задач.
// 15.09.2008 Andy

require_once('../../config.php');
require_once('lib.php');

$ids=get_records_sql('SELECT id FROM mdl_problems where not (id in (select problem_id from ps_problem_subjects))');
foreach ($ids as $id) {

echo '<a href="view3.php?chapterid='.$id->id.'">'.$id->id.'</a> ';}

?>