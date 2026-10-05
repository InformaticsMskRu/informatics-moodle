<?php 

#require_once('../../config.php');


function limit_block($chapter) {
    // Limits are loaded from the API and rendered client-side (js/statement_api.js).
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
 
#echo lang_time_block(1291);
?>
