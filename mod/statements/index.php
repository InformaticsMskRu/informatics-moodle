<?PHP // $Id: index.php,v 1.1.8.1 2007/05/20 06:01:53 skodak Exp $

/// This page lists all the instances of statements in a particular course

require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT);           // Course Module ID

// =========================================================================
// security checks START - teachers and students view
// =========================================================================

if (!$course = get_record('course', 'id', $id)) {
    error('Course ID is incorrect');
}

require_course_login($course, true);

//check all variables
unset($id);

// =========================================================================
// security checks END
// =========================================================================

/// Get all required strings
$strstatementss = get_string('modulenameplural', 'statements');
$strstatements  = get_string('modulename', 'statements');

/// Print the header
if ($course->category) {
    $navigation = '<a href="../../course/view.php?id='.$course->id.'">'.$course->shortname.'</a> ->';
} else {
    $navigation = '';
}

print_header( "$course->shortname: $strstatementss",
               $course->fullname,
               "$navigation $strstatementss",
               '',
               '',
               true,
               '',
               navmenu($course)
             );

add_to_log($course->id, 'statements', 'view all', 'index.php?id='.$course->id, '');

/// Get all the appropriate data
if (!$statementss = get_all_instances_in_course('statements', $course)) {
    notice('There are no statementss', '../../course/view.php?id='.$course->id);
    die;
}

/// Print the list of instances
$strname  = get_string('name');
$strweek  = get_string('week');
$strtopic  = get_string('topic');
$strsummary = get_string('summary');
$strchapters  = get_string('chapterscount', 'statements');

if ($course->format == 'weeks') {
    $table->head  = array ($strweek, $strname, $strsummary, $strchapters);
    $table->align = array ('center', 'left', 'left', 'center');
} else if ($course->format == 'topics') {
    $table->head  = array ($strtopic, $strname, $strsummary, $strchapters);
    $table->align = array ('center', 'left', 'left', 'center');
} else {
    $table->head  = array ($strname, $strsummary, $strchapters);
    $table->align = array ('left', 'left', 'left');
}

$currentsection = '';
foreach ($statementss as $statements) {
    $nocleanoption = new object();
    $nocleanoption->noclean = true;
    $statements->summary = format_text($statements->summary, FORMAT_HTML, $nocleanoption, $course->id);
    $statements->summary = '<span style="font-size:x-small;">'.$statements->summary.'</span>';

    if (!$statements->visible) {
        //Show dimmed if the mod is hidden
        $link = '<a class="dimmed" href="view.php?id='.$statements->coursemodule.'">'.$statements->name.'</a>';
    } else {
        //Show normal if the mod is visible
        $link = '<a href="view.php?id='.$statements->coursemodule.'">'.$statements->name.'</a>';
    }

    $count = count_records('statements_chapters', 'statementsid', $statements->id, 'hidden', '0');

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $printsection = '';
        if ($statements->section !== $currentsection) {
            if ($statements->section) {
                $printsection = $statements->section;
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $statements->section;
        }
        $table->data[] = array ($printsection, $link, $statements->summary, $count);
    } else {
        $table->data[] = array ($link, $statements->summary, $count);
    }
}

echo '<br />';
print_table($table);

print_footer($course);

?>
