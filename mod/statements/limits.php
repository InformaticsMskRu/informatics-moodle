<?php 

#require_once('../../config.php');


function limit_block($chapter) {
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
