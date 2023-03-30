<?php // $Id: index.php,v 1.5 2006/08/28 16:41:20 mark-nielsen Exp $
/**
 * This page lists all the instances of usermonitor in a particular course
 *
 * @author 
 * @version $Id: index.php,v 1.5 2006/08/28 16:41:20 mark-nielsen Exp $
 * @package usermonitor
 **/

/// Replace top5000 with the name of your module

    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id', PARAM_INT);   // course

    if (! $course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }

    require_login($course->id);

    add_to_log($top5000->id, "top5000", "view all", "index.php?id=$top5000->id", "");


/// Get all required strings

    $strtop5000s = get_string("modulenameplural", "top5000");
    $strtop5000  = get_string("modulename", "top5000");


/// Print the header

    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$top5000->id\">$top5000->shortname</a> ->";
    } else {
        $navigation = '';
    }

    print_header("$course->shortname: $strtop5000s", "$course->fullname", "$navigation $strtop5000s", "", "", true, "", navmenu($course));

/// Get all the appropriate data

    if (! $top5000s = get_all_instances_in_course("top5000", $course)) {
        notice("There are no top5000s", "../../course/view.php?id=$course->id");
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");

    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname);
        $table->align = array ("center", "left");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname);
        $table->align = array ("center", "left", "left", "left");
    } else {
        $table->head  = array ($strname);
        $table->align = array ("left", "left", "left");
    }

    foreach ($top5000s as $top5000) {
        if (!$top5000->visible) {
            //Show dimmed if the mod is hidden
            $link = "<a class=\"dimmed\" href=\"view.php?id=$top5000->coursemodule\">$top5000->name</a>";
        } else {
            //Show normal if the mod is visible
            $link = "<a href=\"view.php?id=$top5000->coursemodule\">$top5000->name</a>";
        }

        if ($course->format == "weeks" or $course->format == "topics") {
            $table->data[] = array ($top5000->section, $link);
        } else {
            $table->data[] = array ($link);
        }
    }

//    echo "<br />";

    print_table($table);

/// Finish the page

    print_footer($course);

?>
