<?php 

#require_once('../../config.php');


function limit_block($chapter) {
    // Limits are loaded from the API and rendered client-side (js/statement_api.js).
    // The container is always emitted: it is refilled when another problem is
    // opened without a page reload or an admin flips show_limits with the
    // "Служебное" toggle. The enclosing block is kept hidden until there is
    // something to show (see statement_add_limits() in toc.php).
    $show = $chapter->show_limits ? '1' : '0';
    return "<div class='statement-api-limits' data-problem-id='" . intval($chapter->id)
        . "' data-show-limits='" . $show . "'></div>";
}
 
#echo lang_time_block(1291);
?>
