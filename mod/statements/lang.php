<?php 

require_once('../../config.php');


function lang_time_block($problem_id) {
	global $DB;
    $short_lang = array(1=>'Free Pascal',2=>'GNU C',3=>'GNU C++',8=>'Delphi',18=>'Java',22=>'PHP',23=>'Python 2.7',24=>'Perl',25=>'Mono C#',26=>'Ruby',27=>'Python 3.1',28=>'Haskell',53=>'Go',89=>'Scals',71=>'Kotlin');

    $header = "<tr><td><b>Язык</b></td>";
    $mint = "<tr><td><b>Min время, <i>сек</i></b></td>";
    $avgt = "<tr><td><b>Среднее время, <i>сек</i></b></td>";
    $num = "<tr><td><b>Верных решений</b></td>";
   
    $langs = $DB->get_records('statements_lang', array('problem_id' => $problem_id), '', 'lang_id, maxtime, avgtime, number');
    foreach($langs as $lang) {
      if(array_key_exists($lang->lang_id, $short_lang)) { 
        $header .= "<td><b>" . $short_lang[$lang->lang_id] ."</b></td>";
        $mint .= "<td>" . ($lang->maxtime) . "</td>";
        $avgt .= "<td>" . ($lang->avgtime) . "</td>";
        $num .= "<td>" . ($lang->number) . "</td>";
      }
    }

    $header .= "</tr>\n";
    $mint .= "</tr>\n";
    $avgt .= "</tr>\n";
    $num .= "</tr>\n";

    $table = "<table align=center border=1 cellpadding=3>\n" . $header . $mint . $avgt . $num . "</table>";
    if($langs) {
        return $table;
    }
}
 
?>
