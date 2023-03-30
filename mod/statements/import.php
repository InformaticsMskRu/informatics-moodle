<?PHP // $Id: import.php,v 1.2.8.1 2007/05/20 06:01:54 skodak Exp $

require_once('../../config.php');
require_once('lib.php');

$id         = required_param('id', PARAM_INT);           // Course Module ID
$subchapter = optional_param('subchapter', 0, PARAM_BOOL);
$cancel     = optional_param('cancel', 0, PARAM_BOOL);

// =========================================================================
// security checks START - only teachers edit
// =========================================================================
require_login();

if (!$cm = get_coursemodule_from_id('statements', $id)) {
    error('Course Module ID was incorrect');
}

if (!$course = get_record('course', 'id', $cm->course)) {
    error('Course is misconfigured');
}

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('moodle/course:manageactivities', $context);

if (!$statements = get_record('statements', 'id', $cm->instance)) {
    error('Course module is incorrect');
}

//check all variables
unset($id);

// =========================================================================
// security checks END
// =========================================================================

///cancel pressed, go back to statements
if ($cancel) {
    redirect('view.php?id='.$cm->id);
    die;
}

///prepare the page header
$strstatements = get_string('modulename', 'statements');
$strstatementss = get_string('modulenameplural', 'statements');
$strimport = get_string('import', 'statements');

if ($course->category) {
    $navigation = '<a href="../../course/view.php?id='.$course->id.'">'.$course->shortname.'</a> ->';
} else {
    $navigation = '';
}

print_header( "$course->shortname: $statements->name",
              $course->fullname,
              "$navigation <a href=\"index.php?id=$course->id\">$strstatementss</a> -> <a href=\"view.php?id=$cm->id\">$statements->name</a> -> $strimport",
              '',
              '',
              true,
              '',
              ''
            );

/// If data submitted, then process, store and relink.
if (($form = data_submitted()) && (confirm_sesskey())) {
    $form->reference = stripslashes($form->reference);
    if ($form->reference != '') { //null path is root
        $form->reference = statements_prepare_link($form->reference);
        if ($form->reference == '') { //evil characters in $ref!
            error('Invalid character detected in given path!');
        }
    }
    $coursebase = $CFG->dataroot.'/'.$statements->course;
    if ($form->reference == '') {
        $base = $coursebase;
    } else {
        $base = $coursebase.'/'.$form->reference;
    }

    //prepare list of html files in $refs
    $refs = array();
    $htmlpat = '/\.html$|\.htm$/i';
    if (is_dir($base)) { //import whole directory
        $basedir = opendir($base);
        while ($file = readdir($basedir)) {
            $path = $base.'/'.$file;
            if (filetype($path) == 'file' and preg_match($htmlpat, $file)) {
                $refs[] = str_replace($coursebase, '', $path);
            }
        }
        asort($refs);
    } else if (is_file($base)) { //import single file
        $refs[] = '/'.$form->reference;
    } else { //what is it???
        error('Incorrect file/directory specified!');
    }

    //import files
    echo '<center>';
    echo '<b>'.get_string('importing', 'statements').':</b>';
    echo '<table cellpadding="2" cellspacing="2" border="1">';
    statements_check_structure($statements->id);
    foreach($refs as $ref) {
        $chapter = statements_read_chapter($coursebase, $ref);
        if ($chapter) {
            $chapter->title = addslashes($chapter->title);
            $chapter->content = addslashes($chapter->content);
            $chapter->importsrc = addslashes($chapter->importsrc);
            $chapter->statementsid = $statements->id;
            $chapter->pagenum = count_records('statements_chapters', 'statementsid', $statements->id)+1;
            $chapter->timecreated = time();
            $chapter->timemodified = time();
            echo "imsrc:".$chapter->importsrc;
            if (($subchapter) || preg_match('/_sub\.htm/i', $chapter->importsrc)) { //if filename or directory starts with sub_* treat as subdirecotories
                $chapter->subchapter = 1;
            } else {
                $chapter->subchapter = 0;
            }
            if (!$chapter->id = insert_record('statements_chapters', $chapter)) {
                error('Could not update your statements');
            }
            add_to_log($course->id, 'course', 'update mod', '../mod/statements/view.php?id='.$cm->id, 'statements '.$statements->id);
            add_to_log($course->id, 'statements', 'update', 'view.php?id='.$cm->id.'&chapterid='.$chapter->id, $statements->id, $cm->id);
        }
    }
    echo '</table><br />';
    echo '<b>'.get_string('relinking', 'statements').':</b>';
    echo '<table cellpadding="2" cellspacing="2" border="1">';
    //relink whole statements = all chapters
    statements_relink($cm->id, $statements->id, $course->id);
    echo '</table><br />';
    echo '<a href="view.php?id='.$cm->id.'">'.get_string('continue').'</a>';
    echo '</center>';
} else {
/// Otherwise fill and print the form.
    $strdoimport = get_string('doimport', 'statements');
    $strchoose = get_string('choose');
    $pageheading = get_string('importingchapters', 'statements');

    $icon = '<img align="absmiddle" height="16" width="16" src="icon_chapter.gif" />&nbsp;';
    print_heading_with_help($pageheading, 'import', 'statements', $icon);
    print_simple_box_start('center', '');
    ?>
    <form name="theform" method="post" action="import.php">
    <table cellpadding="5" align="center">
    <tr valign="top">
        <td valign="top" align="right">
            <b><?php print_string('fileordir', 'statements') ?>:</b>
        </td>
        <td>
            <?php
              echo '<input id="id_reference" name="reference" size="40" value="" />&nbsp;';
              button_to_popup_window ('/mod/statements/coursefiles.php?choose=id_reference&id='.$course->id,
                                      'coursefiles', $strchoose, 500, 750, $strchoose);
            ?>
        </td>
    </tr>
    <tr valign="top">
        <td valign="top" align="right">
            <b><?php print_string('subchapter', 'statements') ?>:</b>
        </td>
        <td>
        <?php
            echo '<input name="subchapter" type="checkbox" value="1" />';
        ?>
        </td>
    </tr>
    <tr valign="top">
        <td valign="top" align="right">&nbsp;</td>
        <td><p><?php print_string('importinfo', 'statements') ?></p></td>
    </tr>
    </table>
    <center>
        <input type="submit" value="<?php echo $strdoimport ?>" />
        <input type="submit" name="cancel" value="<?php print_string("cancel") ?>" />
    </center>
        <input type="hidden" name="id" value="<?php p($cm->id) ?>" />
        <input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>" /> 
    </form>

    <?php
    print_simple_box_end();
}

print_footer($course);

?>
