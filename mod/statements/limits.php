<?php 

#require_once('../../config.php');


function limit_block($chapter) {
    // Limits are loaded from the API and rendered client-side
    // (js/statement_api.js). Keep the same "limits exist" condition so the
    // sidebar block still appears only when there are limits.
    if (statements_use_api_rendering()) {
        // Admins can flip show_limits with the "Служебное" toggle without a page
        // reload, so emit the container for them even when limits are hidden —
        // it is the target js/module.js refreshes. The enclosing block is kept
        // hidden until shown (see statement_add_limits() in toc.php).
        $can_toggle = has_capability('moodle/site:edit_problem', context_system::instance());
        if ($chapter->memorylimit && ($chapter->show_limits || $can_toggle)) {
            $show = $chapter->show_limits ? '1' : '0';
            return "<div class='statement-api-limits' data-problem-id='" . intval($chapter->id)
                . "' data-show-limits='" . $show . "'></div>";
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
