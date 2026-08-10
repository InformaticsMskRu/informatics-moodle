<?php 

#require_once('../../config.php');


function limit_block($chapter) {
    global $USER;
    // Experimental, user 469 only: time/memory limits are loaded from the API and
    // rendered client-side (js/statement_api.js). Keep the same "limits exist"
    // condition so the sidebar block still appears only when there are limits.
    if ($USER->id == 469) {
        if ($chapter->memorylimit && $chapter->show_limits) {
            return "<div class='statement-api-limits' data-problem-id='" . intval($chapter->id) . "'></div>";
        }
        return '';
    }

    $t_val = floor(($chapter->timelimit) * 100) / 100.0;
    $m_val = ($chapter->memorylimit / 1024.0 / 1024.0);

    $table = '';
    if($chapter->memorylimit && $chapter->show_limits) {
        if ($t_val > 0) {
          $table .= '<i class="icon fa fa-clock-o fa-fw " aria-hidden="true"></i>';
          $table .= $t_val." сек.<br/>";
        }
        $table .='<i class="icon fa fa-table fa-fw " aria-hidden="true"></i>';
        $table .= $m_val." MiB<br/>";
    }
    return $table;
}
 
#echo lang_time_block(1291);
?>
