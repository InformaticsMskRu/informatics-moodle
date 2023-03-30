<?PHP // $Id: lib.php,v 1.1.8.1 2007/05/20 06:02:01 skodak Exp $

define('NUM_NONE',     '0');
define('NUM_NUMBERS',  '1');
define('NUM_BULLETS',  '2');
define('NUM_INDENTED', '3');


/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function statements_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (isguestuser($user)) {
        // The guest user cannot post, so it is not possible to view any posts.
        // May as well just bail aggressively here.
        return false;
    }
    $postsurl = new moodle_url('/submits/view.php', array('user_id' => $user->id));
    if (!empty($course)) {
        $postsurl->param('course', $course->id);
    }
    $string = get_string('usersubmits', 'mod_forum');
    $node = new core_user\output\myprofile\node('miscellaneous', 'usersubmits', "Посылки", null, $postsurl);
    $tree->add_node($node);

    return true;
}

function statements_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $statementnode) {
    global $USER, $PAGE, $OUTPUT, $DB;
}

if (!isset($CFG->statements_tocwidth)) {
    set_config("statements_tocwidth", 180);  // default toc width
}

if (!isset($CFG->statements_tocwidth)) {
    set_config("statements_tocwidth", 180);  // default toc width
}

function print_timer($finish, $now, $text) 
{
	$res = '<script type="text/javascript" language="javascript">

	function runMultiple()
	{
			time -= 1;
			var h = Math.floor(time/3600);
			var m = Math.floor((time-Math.floor(time/3600)*3600)/60);
			m = m>9?m:"0"+m;
			var s = time%60;
			s = s>9?s:"0"+s;
			document.getElementById("counter").innerHTML = "<div align=\'center\'>'.$text.' <b>"+h+":"+m+":"+s+"</b></div>";		
	}
	var time='.($finish - $now).';
	var timer = window.setInterval("if (time>0){runMultiple();} else {location.reload(true);}", 1000);

	</script>';
    return $res;
}

function statements_get_numbering_types() {
    return array (NUM_NONE       => get_string('numbering0', 'statements'),
                  NUM_NUMBERS    => get_string('numbering1', 'statements'),
                  NUM_BULLETS    => get_string('numbering2', 'statements'),
                  NUM_INDENTED   => get_string('numbering3', 'statements') );
}

/// Library of functions and constants for module 'statements'

function statements_add_instance($statements) {
/// Given an object containing all the necessary data,
/// (defined by the form in mod.html) this function
/// will create a new instance and return the id number
/// of the new instance.
    global $DB;
    $statements->timecreated = time();
    $statements->timemodified = $statements->timecreated;
    if (!isset($statements->customtitles)) {
        $statements->customtitles = 0;
    }
    if (!isset($statements->olympiad)) {
        $statements->olympiad = 0;
    }
    if (!isset($statements->virtual_olympiad)) {
        $statements->virtual_olympiad = 0;
    }

    if (!isset($statements->virtual_duration)) {
        $statements->virtual_duration = 0;
    }        
    if (!isset($statements->disableprinting)) {
        $statements->disableprinting = 0;
    }

    return $DB->insert_record('statements', $statements);
}


function statements_update_instance($statements) {
/// Given an object containing all the necessary data,
/// (defined by the form in mod.html) this function
/// will update an existing instance with new data.
    global $DB;
    $statements->timemodified = time();
    $statements->id = $statements->instance;
    if (!isset($statements->customtitles)) {
        $statements->customtitles = 0;
    }
    if (!isset($statements->olympiad)) {
        $statements->olympiad = 0;
    }

    if (!isset($statements->virtual_olympiad)) {
        $statements->virtual_olympiad = 0;
    }

    if (!isset($statements->virtual_duration)) {
        $statements->virtual_duration = 0;
    }    

    if (!isset($statements->disableprinting)) {
        $statements->disableprinting = 0;
    }

    # May have to add extra stuff in here #

    return $DB->update_record('statements', $statements);
}


function statements_delete_instance($id) {
/// Given an ID of an instance of this module,
/// this function will permanently delete the instance
/// and any data that depends on it.
    global $DB;

    if (! $statements = $DB->get_record('statements', array('id'=>$id))) {
        return false;
    }

    $result = true;

    $DB->delete_records('statements_chapters', array('statementsid'=> $statements->id));

    if (! $DB->delete_records('statements', array('id'=> $statements->id))) {
        $result = false;
    }

    return $result;
}


function statements_get_types() {
    global $CFG;

    $types = array();

    $type = new object();
    $type->modclass = MOD_CLASS_RESOURCE;
    $type->type = 'statements';
    $type->typestr = get_string('modulename', 'statements');
    $types[] = $type;

    return $types;
}
function statements_user_outline($course, $user, $mod, $statements) {
/// Return a small object with summary information about what a
/// user has done with a given particular instance of this module
/// Used for user activity reports.
/// $return->time = the time they did it
/// $return->info = a short text description

    $return = null;
    return $return;
}

function statements_user_complete($course, $user, $mod, $statements) {
/// Print a detailed representation of what a  user has done with
/// a given particular instance of this module, for user activity reports.

    return true;
}

function statements_print_recent_activity($course, $isteacher, $timestart) {
/// Given a course and a time, this module should find recent activity
/// that has occurred in statements activities and print it out.
/// Return true if there was output, or false is there was none.

    global $CFG;

    return false;  //  True if anything was printed, otherwise false
}

function statements_cron () {
/// Function to be run periodically according to the moodle cron
/// This function searches for things that need to be done, such
/// as sending out mail, toggling flags etc ...

    global $CFG;

    return true;
}

function statements_grades($statementsid) {
/// Must return an array of grades for a given instance of this module,
/// indexed by user.  It also returns a maximum allowed grade.

    return NULL;
}

function statements_get_participants($statementsid) {
//Must return an array of user records (all data) who are participants
//for a given instance of statements. Must include every user involved
//in the instance, independient of his role (student, teacher, admin...)
//See other modules as example.

    return false;
}

function statements_scale_used ($statementsid,$scaleid) {
//This function returns if a scale is being used by one statements
//it it has support for grading and scales. Commented code should be
//modified if necessary. See forum, glossary or journal modules
//as reference.

    $return = false;

    //$rec = get_record('statements','id',$statementsid,'scale',"-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}

    return $return;
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other statements functions go here.  Each of them must have a name that
/// starts with statements_

//check chapter ordering and
//make sure subchapter is not first in statements
//hidden chapter must have all subchapters hidden too
function statements_check_structure($statementsid) {
    global $DB;
    if ($chapters = $DB->get_records('statements_chapters', array('statementsid'=>$statementsid), 'pagenum', 'id, pagenum, subchapter, hidden')) {
        $first = true;
        $hidesub = true;
        $i = 1;
        foreach($chapters as $ch) {
            if ($first and $ch->subchapter) {
                $ch->subchapter = 0;
            }
            $first = false;
            if (!$ch->subchapter) {
                $hidesub = $ch->hidden;
            } else {
                $ch->hidden = $hidesub ? true : $ch->hidden;
            }
            $ch->pagenum = $i;
            $DB->update_record('statements_chapters', $ch);
            $i++;
        }
    }
}

function statements_edit_button($id, $courseid, $chapterid) {
    global $CFG, $USER;

	if (empty($courseid)) {
		$context = context_system::instance();
	} else {
		$context = context_course::instance($courseid);
	}


   if (has_capability('moodle/statements:edit', $context, $USER->id, false)) {
        if (!empty($USER->editing)) {
            $string = get_string("turneditingoff");
            $edit = '0';
        } else {
            $string = get_string("turneditingon");
            $edit = '1';
        }
        return '<form method="get" action="'.$CFG->wwwroot.'/mod/statements/view.php"><div>'.
               '<input type="hidden" name="id" value="'.$id.'" />'.
               '<input type="hidden" name="chapterid" value="'.$chapterid.'" />'.
               '<input type="hidden" name="edit" value="'.$edit.'" />'.
               '<input type="submit" value="'.$string.'" /></div></form>';
    } else {
        return '';
    }
}
//button EDIT for the single problem (not in the course)
function problem_edit_button($chapterid) {
    global $CFG, $USER;

    $string = get_string("turneditingon");
    return '<form method="get" action="'.$CFG->wwwroot.'/mod/statements/edit.php"><div>'.
           '<input type="hidden" name="chapterid" value="'.$chapterid.'" />'.
//           '<input type="hidden" name="edit" value="'.$edit.'" />'.
           '<input type="submit" value="'.$string.'" /></div></form>';
}

/// general function for logging to table
function statements_log($str1, $str2, $level = 0) {
    switch ($level) {
        case 1:
            echo '<tr><td><span class="dimmed_text">'.$str1.'</span></td><td><span class="dimmed_text">'.$str2.'</span></td></tr>';
            break;
        case 2:
            echo '<tr><td><span style="color: rgb(255, 0, 0);">'.$str1.'</span></td><td><span style="color: rgb(255, 0, 0);">'.$str2.'</span></td></tr>';
            break;
        default:
            echo '<tr><td>'.$str1.'</class></td><td>'.$str2.'</td></tr>';
            break;
    }
}

//=================================================
// import functions
//=================================================

/// normalize relative links (= remove ..)
function statements_prepare_link($ref) {
    if ($ref == '') {
        return '';
    }
    $ref = str_replace('\\','/',$ref); //anti MS hack
    $cnt = substr_count($ref, '..');
    for($i=0; $i<$cnt; $i++) {
        $ref = ereg_replace('[^/]+/\.\./', '', $ref);
    }
    //still any '..' left?? == error! error!
    if (substr_count($ref, '..') > 0) {
        return '';
    }
    if (ereg('[\|\`]', $ref)) {  // check for other bad characters
        return '';
    }
    return $ref;
}

/// read chapter content from file
function statements_read_chapter($base, $ref) {
    $file = $base.'/'.$ref;
    if (filesize($file) <= 0 or !is_readable($file)) {
        statements_log($ref, get_string('error'), 2);
        return;
    }
    //first read data
    $handle = fopen($file, "rb");
    $contents = fread($handle, filesize($file));
    fclose($handle);
    //extract title
    if (preg_match('/<title>([^<]+)<\/title>/i', $contents, $matches)) {
        $chapter->title = $matches[1];
    } else {
        $chapter->title = $ref;
    }
    //extract page body
    if (preg_match('/<body[^>]*>(.+)<\/body>/is', $contents, $matches)) {
        $chapter->content = $matches[1];
    } else {
        statements_log($ref, get_string('error'), 2);
        return;
    }
    statements_log($ref, get_string('ok'));
    $chapter->importsrc = $ref;
    //extract page head
    if (preg_match('/<head[^>]*>(.+)<\/head>/is', $contents, $matches)) {
        $head = $matches[1];
        if (preg_match('/charset=([^"]+)/is', $head, $matches)) {
            $enc = $matches[1];
            $textlib = textlib_get_instance();
            $chapter->content = $textlib->convert($chapter->content, $enc, current_charset());
            $chapter->title = $textlib->convert($chapter->title, $enc, current_charset());
        }
        if (preg_match_all('/<link[^>]+rel="stylesheet"[^>]*>/i', $head, $matches)) { //dlnsk extract links to css
            for($i=0; $i<count($matches[0]); $i++){
                $chapter->content = $matches[0][$i]."\n".$chapter->content;
            }
        }
    }
    return $chapter;
}

///relink images and relative links
function statements_relink($id, $statementsid, $courseid) {
    global $CFG;
    if ($CFG->slasharguments) {
        $coursebase = $CFG->wwwroot.'/file.php/'.$courseid;
    } else {
        $coursebase = $CFG->wwwroot.'/file.php?file=/'.$courseid;
    }
    $chapters = $DB->get_records('statements_chapters', array('statementsid'=>$statementsid), 'pagenum', 'id, pagenum, title, content, importsrc');
    $originals = array();
    foreach($chapters as $ch) {
        $originals[$ch->importsrc] = $ch;
    }
    foreach($chapters as $ch) {
        $rel = substr($ch->importsrc, 0, strrpos($ch->importsrc, '/')+1);
        $base = $coursebase.strtr(urlencode($rel), array("%2F" => "/"));  //for better internationalization (dlnsk) 
        $modified = false;
        //image relinking
        if ($ch->importsrc && preg_match_all('/(<img[^>]+src=")([^"]+)("[^>]*>)/i', $ch->content, $images)) {
            for($i = 0; $i<count($images[0]); $i++) {
                if (!preg_match('/[a-z]+:/i', $images[2][$i])) { // not absolute link
                    $link = statements_prepare_link($base.$images[2][$i]);
                    if ($link == '') {
                        continue;
                    }
                    $origtag = $images[0][$i];
                    $newtag = $images[1][$i].$link.$images[3][$i];
                    $ch->content = str_replace($origtag, $newtag, $ch->content);
                    $modified = true;
                    statements_log($ch->title, $images[2][$i].' --> '.$link);
                }
            }
        }
        //css relinking (dlnsk)
        if ($ch->importsrc && preg_match_all('/(<link[^>]+href=")([^"]+)("[^>]*>)/i', $ch->content, $csslinks)) {
            for($i = 0; $i<count($csslinks[0]); $i++) {
                if (!preg_match('/[a-z]+:/i', $csslinks[2][$i])) { // not absolute link
                    $link = statements_prepare_link($base.$csslinks[2][$i]);
                    if ($link == '') {
                        continue;
                    }
                    $origtag = $csslinks[0][$i];
                    $newtag = $csslinks[1][$i].$link.$csslinks[3][$i];
                    $ch->content = str_replace($origtag, $newtag, $ch->content);
                    $modified = true;
                    statements_log($ch->title, $csslinks[2][$i].' --> '.$link);
                }
            }
        }
        //general embed relinking - flash and others??
        if ($ch->importsrc && preg_match_all('/(<embed[^>]+src=")([^"]+)("[^>]*>)/i', $ch->content, $embeds)) {
            for($i = 0; $i<count($embeds[0]); $i++) {
                if (!preg_match('/[a-z]+:/i', $embeds[2][$i])) { // not absolute link
                    $link = statements_prepare_link($base.$embeds[2][$i]);
                    if ($link == '') {
                        continue;
                    }
                    $origtag = $embeds[0][$i];
                    $newtag = $embeds[1][$i].$link.$embeds[3][$i];
                    $ch->content = str_replace($origtag, $newtag, $ch->content);
                    $modified = true;
                    statements_log($ch->title, $embeds[2][$i].' --> '.$link);
                }
            }
        }
        //flash in IE <param name=movie value="something" - I do hate IE!
        if ($ch->importsrc && preg_match_all('/<param[^>]+name\s*=\s*"?movie"?[^>]*>/i', $ch->content, $params)) {
            for($i = 0; $i<count($params[0]); $i++) {
                if (preg_match('/(value=\s*")([^"]+)(")/i', $params[0][$i], $values)) {
                    if (!preg_match('/[a-z]+:/i', $values[2])) { // not absolute link
                        $link = statements_prepare_link($base.$values[2]);
                        if ($link == '') {
                            continue;
                        }
                        $newvalue = $values[1].$link.$values[3];
                        $newparam = str_replace($values[0], $newvalue, $params[0][$i]);
                        $ch->content = str_replace($params[0][$i], $newparam, $ch->content);
                        $modified = true;
                        statements_log($ch->title, $values[2].' --> '.$link);
                    }
                }
            }
        }
        //java applet - add code bases if not present!!!!
        if ($ch->importsrc && preg_match_all('/<applet[^>]*>/i', $ch->content, $applets)) {
            for($i = 0; $i<count($applets[0]); $i++) {
                if (!stripos($applets[0][$i], 'codebase')) {
                    $newapplet = str_ireplace('<applet', '<applet codebase="."', $applets[0][$i]);
                    $ch->content = str_replace($applets[0][$i], $newapplet, $ch->content);
                    $modified = true;
                }
            }
        }
        //relink java applet code bases
        if ($ch->importsrc && preg_match_all('/(<applet[^>]+codebase=")([^"]+)("[^>]*>)/i', $ch->content, $codebases)) {
            for($i = 0; $i<count($codebases[0]); $i++) {
                if (!preg_match('/[a-z]+:/i', $codebases[2][$i])) { // not absolute link
                    $link = statements_prepare_link($base.$codebases[2][$i]);
                    if ($link == '') {
                        continue;
                    }
                    $origtag = $codebases[0][$i];
                    $newtag = $codebases[1][$i].$link.$codebases[3][$i];
                    $ch->content = str_replace($origtag, $newtag, $ch->content);
                    $modified = true;
                    statements_log($ch->title, $codebases[2][$i].' --> '.$link);
                }
            }
        }
        //relative link conversion
        if ($ch->importsrc && preg_match_all('/(<a[^>]+href=")([^"^#]*)(#[^"]*)?("[^>]*>)/i', $ch->content, $links)) {
            for($i = 0; $i<count($links[0]); $i++) {
                if ($links[2][$i] != ''                         //check for inner anchor links
                && !preg_match('/[a-z]+:/i', $links[2][$i])) { //not absolute link
                    $origtag = $links[0][$i];
                    $target = statements_prepare_link($rel.$links[2][$i]); //target chapter
                    if ($target != '' && array_key_exists($target, $originals)) {
                        $o = $originals[$target];
                        $newtag = $links[1][$i].$CFG->wwwroot.'/mod/statements/view.php?id='.$id.'&chapterid='.$o->id.$links[3][$i].$links[4][$i];
                        $newtag = preg_replace('/target=[^\s>]/i','', $newtag);
                        $ch->content = str_replace($origtag, $newtag, $ch->content);
                        $modified = true;
                        statements_log($ch->title, $links[2][$i].$links[3][$i].' --> '.$CFG->wwwroot.'/mod/statements/view.php?id='.$id.'&chapterid='.$o->id.$links[3][$i]);
                    } else if ($target!='' && (!preg_match('/\.html$|\.htm$/i', $links[2][$i]))) { // other relative non html links converted to download links
                        $target = statements_prepare_link($base.$links[2][$i]);
                        $origtag = $links[0][$i];
                        $newtag = $links[1][$i].$target.$links[4][$i];
                        $ch->content = str_replace($origtag, $newtag, $ch->content);
                        $modified = true;
                        statements_log($ch->title, $links[2][$i].' --> '.$target);
                    }
                }
            }
        }
        if ($modified) {
            $ch->title = addslashes($ch->title);
            $ch->content = addslashes($ch->content);
            $ch->importsrc = addslashes($ch->importsrc);
            if (!$DB->update_record('statements_chapters', $ch)) {
                error('Could not update your statements');
            }
        }
    }
}

?>
