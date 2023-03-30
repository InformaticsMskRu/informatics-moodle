<?php  // $Id: view.php,v 1.4 2006/08/28 16:41:20 mark-nielsen Exp $
/**
 * This page prints a particular instance of contest
 * 
 * @author 
 * @version $Id: view.php,v 1.4 2006/08/28 16:41:20 mark-nielsen Exp $
 * @package contest
 **/

/// (Replace contest with the name of your module)

    require_once("../../config.php");
    require_once("lib.php");

    $id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
//    $contest_id  = optional_param('contest_id', 0, PARAM_INT);  // contest ID
    if ($id) {
        if (! $cm = $DB->get_record("course_modules", array("id" => $id))) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
            error("Course is misconfigured");
        }
    
        if (! $monitor = $DB->get_record("monitor", array("id" => $cm->instance))) {
            error("Course module is incorrect");
        }

    } else {
        if (! $monitor = $DB->get_record("monitor", array("id" => $a))) {
            error("Course module is incorrect");
        }
        if (! $course = $DB->get_record("course", array("id" => $monitor->course))) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("monitor", $monitor->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }
    require_login($course->id);

    add_to_log($course->id, "monitor", "view", "view.php?id=$cm->id", "$monitor->id");

/// Print the page header

    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = '';
    }

    $strcontests = get_string("modulenameplural", "monitor");
    $strcontest  = get_string("modulename", "monitor");
    $PAGE->set_url('/mod/monitor/view.php', array('id' => $cm->id));
    $PAGE->requires->jquery();
    $PAGE->set_heading("$course->shortname: $monitor->name"); // Required
    $PAGE->set_title($course->fullname);
    $PAGE->set_cacheable(false);
    $PAGE->set_focuscontrol($focus);
    $PAGE->navbar->add("<a href=index.php?id=$course->id>$strmonitors</a>");
    $PAGE->navbar->add($monitor->name);

    echo $OUTPUT->header();
    $user_set = array();

    if (array_key_exists('users', $_GET)) {
         $user_set = $_GET['users']; // Course Module ID, or
    }
			$monitor_id = $monitor->monitor_id;
			$group_id = $monitor->group_id;
    $content = "<div id='monitor_table'><div class='d-flex justify-content-center'><div class='spinner-border text-primary' role='status'><span class='sr-only'>Loading...</span></div></div></div>";
    echo format_text($content, FORMAT_HTML, array('noclean'=>true), $course->id);
    echo "<script type='text/javascript'>";
    echo "function background_monitor_loading(){";
    echo "    var contest_id = " . $cm->id . ";";
    echo "    var url = '/py/monitor?contest_id=' + contest_id;";
     
    if ((int)$group_id > 0) {
         echo "var group_id = ".$group_id.";";
         echo "url += '&group_id=' + group_id;";
    }

    echo "    jQuery.get(url, (data) => jQuery('#monitor_table').html(data));";
    echo "}";
    echo "setTimeout(background_monitor_loading, 0);";
    echo "</script>";

    echo $OUTPUT->footer();
    $user_id = $USER->id;
?>
