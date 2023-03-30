<?php  // $Id: view.php,v 1.4 2006/08/28 16:41:20 mark-nielsen Exp $
/**
 * This page prints a particular instance of contest
 * 
 * @author 
 * @version $Id: view.php,v 1.4 2006/08/28 16:41:20 mark-nielsen Exp $
 * @package monitorgroup
 **/

/// (Replace contest with the name of your module)

    require_once("../../config.php");
    require_once("lib.php");

    $id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    if ($id) {
        if (! $cm = get_record("course_modules", "id", $id)) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
    
        if (! $monitor = get_record("monitorgroup", "id", $cm->instance)) {
            error("Course module is incorrect");
        }

    } else {
        if (! $monitor = get_record("monitorgroup", "id", $a)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $monitor->course)) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("monitorgroup", $monitor->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }
    require_login($course->id);

    add_to_log($course->id, "monitorgroup", "view", "view.php?id=$cm->id", "$monitor->id");

/// Print the page header

    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = '';
    }

    $strcontests = get_string("modulenameplural", "monitor");
    $strcontest  = get_string("modulename", "monitor");

    print_header("$course->shortname: $monitor->name", "$course->fullname",
                 "$navigation <a href=index.php?id=$course->id>$strmonitors</a> -> $monitor->name", 
                  "", '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/test_monitor/Styles/main.css"/>', true, update_module_button($cm->id, $course->id, $strcontest), 
                 navmenu($course, $cm));

			$monitor_id = $monitor->monitor_id;
			$groups = get_records_sql("SELECT mdl_ejudge_group.* ".
					 "FROM mdl_ejudge_group left join mdl_ejudge_group_users on  mdl_ejudge_group.id = mdl_ejudge_group_users.group_id ".
					 "WHERE mdl_ejudge_group.visible=1 ".
					 " AND (mdl_ejudge_group_users.user_id=".$USER->id." OR  mdl_ejudge_group.owner_id=".$USER->id.")");
?>
  <link href="css/bootstrap.css" rel="stylesheet">
  <style>
    .container { margin-top:80px; }
    .nav-tabs li a:focus { outline:none; }
  </style>
  <script src="js/jquery.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/bootstrap-tab.js"></script>
            <ul id="myTab" class="nav nav-tabs">
<?php			$flag = 0;
                     foreach ($groups as $gr_id => $group) {
                            if ($flag == 0) {$flag=1; echo "<li class='active'><a href='#$group->id' data-toggle='tab'>$group->name</a></li>";}
                            else {echo "<li><a href='#$group->id' data-toggle='tab'>$group->name</a></li>";}
			}
            echo "</ul><div class='tab-content' style='overflow:hidden;display:inline;'>";
			$flag = 0;
                     foreach ($groups as $gr_id => $group) {
	       		$group_id = $group -> id;
				if ($flag == 0) {$flag=1; echo "<div class='tab-pane fade in active' id=$group_id>";}
                            else {echo "<div class='tab-pane fade' id=$group_id>";}

				require('../../test_monitor/new_monitor.php');
                            echo "</div>";
			}
           echo "</div>";
/// Finish the page
    print_footer($course);

?>
