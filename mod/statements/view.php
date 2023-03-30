<?PHP // $Id: view.php,v 1.2.8.2 2007/06/17 10:36:37 stronk7 Exp $

require_once('../../config.php');
require_once('lib.php');
require_once('lang.php');
require_once('limits.php');

global $url;

$id        = optional_param('id', 0, PARAM_INT);           // Course Module ID 
$user_id_for_submits = optional_param('user_id', 0, PARAM_INT);           

$chapterid = optional_param('chapterid', 0, PARAM_INT); // Chapter ID
$edit      = optional_param('edit', -1, PARAM_BOOL);     // Edit mode
$register  = optional_param('register', 0, PARAM_INT);     // Try to register into olympiad; only for olympiad contest
$olymp     = optional_param('olymp', 2, PARAM_INT);     // Olymp monitor type: 0 - all, 3 - only olympiad submits, 2 - olymp and upsolving, 1 - only before olymp
$end_olymp  = optional_param('end_olymp', 0, PARAM_INT);
$mon_type = optional_param('mon_type', 0, PARAM_INT);
$run_id = optional_param('run_id', 0, PARAM_ALPHANUM);


// =========================================================================
// security checks START - teachers edit; students view
// =========================================================================
if (!$cm = get_coursemodule_from_id('statements', $id)) {
    $without_course = true;
} else {
    $without_course = false;
}

function isadmin() {
    return is_siteadmin();
	global $cm;
	$context = context_module::instance($cm->id);
	return has_capability('mod/statements:viewall', $context);
}

if ( !$without_course && !($course = $DB->get_record('course', array('id' => $cm->course)))) {
    error('Course is misconfigured');
}

if (!$without_course && !($statements = $DB->get_record('statements', array('id' => $cm->instance)))) {
    error('Course module is incorrect');
}

if (!$without_course && $statements && $statements->virtual_duration == 0) {
    $statements->virtual_duration = 5 * 60 * 60;
}
if (!$without_course) {
    require_course_login($course, true, $cm);
    $context = context_module::instance($cm->id);
    $allowedit = has_capability('moodle/course:manageactivities', $context);
    $viewhidden = has_capability('mod/book:viewhiddenchapters', $context);
} else {
    $context = context_system::instance();
    $allowedit = has_capability('moodle/site:edit_problem', $context);
    $viewhidden = false;
}
if ($allowedit) {
    if ($edit != -1  and confirm_sesskey()) {
        $USER->editing = $edit;
    } else {
        if (isset($USER->editing)) {
            $edit = $USER->editing;
        } else {
            $edit = 0;
		}
    }
} else {
    $edit = 0;
}

$usertype= $DB->get_record_sql("SELECT data FROM mdl_user_info_data WHERE userid = ".$USER->id." AND fieldid=6");

if (!$without_course)
{
    $chapters_new = $DB->get_records_sql('
        SELECT 
            mdl_problems.id, 
            mdl_statements_problems_correlation.rank,
            mdl_problems.name,
            mdl_statements_problems_correlation.hidden as cur_hidden
        FROM
            mdl_problems, mdl_statements_problems_correlation
        WHERE 
            mdl_statements_problems_correlation.problem_id = mdl_problems.id AND
            mdl_statements_problems_correlation.statement_id = '.$statements->id.' 
        ORDER BY
            mdl_statements_problems_correlation.rank');
} else {
    $select = "";
    $chapters_new = $DB->get_records_sql('
        SELECT 
            mdl_problems.id, 
            mdl_problems.name
        FROM
            mdl_problems
        WHERE mdl_problems.id='.$chapterid);
}

if (!$chapters_new && !$without_course) {
    if ($allowedit) {
		redirect('../../course/modedit.php?return=0&update='.$cm->id); //no chapters - add new one
    } else {
        if (!$without_course)
        {
            error('Error reading statements chapters.');
        }
    }
}
/// check chapterid and read chapter data
if ($chapterid == '0') { // go to first chapter if no given
    foreach($chapters_new as $ch) {
        if ($allowedit) {
            $chapterid = $ch->id;
            break;
        }
        if (!$ch->cur_hidden) {
            $chapterid = $ch->id;
            break;
        }
    }
}

if (isset($chapterid)) {
  $problem_id = $chapterid;
}

if (!$without_course)
{
    if (!$chapter = $DB->get_record_sql('SELECT mdl_problems.*,
            mdl_statements_problems_correlation.statement_id 
        FROM
            mdl_problems, mdl_statements_problems_correlation
        WHERE 
            mdl_problems.id = mdl_statements_problems_correlation.problem_id AND
            mdl_statements_problems_correlation.statement_id = '.$statements->id.' AND
            mdl_problems.id = '.$problem_id)) {
	}
} else {
    if (!$chapter = $DB->get_record_sql('SELECT 
            mdl_problems.*
        FROM
            mdl_problems
        WHERE 
        mdl_problems.id = '.$problem_id)) {
        error('Error reading statements chapters.'.$problem_id);
	}
}
//check all variables
$mod_id = $id;
unset($id);

/// chapter is hidden for students
if (!$allowedit and isset($chapter->cur_hidden) && $chapter->cur_hidden) {
    error('Error reading statements chapters.');
}

/// chapter not part of this statements!
if (!$without_course && $chapter && $chapter->statement_id != $statements->id) {
    error('Chapter not part of this statements!');
}
// =========================================================================
// security checks  END
// =========================================================================

//add_to_log($course->id, 'statements', 'view', 'view.php?id='.$cm->id.'&amp;chapterid='.$problem_id, $statements->id, $cm->id);

///read standard strings
$strstatementss = get_string('modulenameplural', 'statements');
$strstatements  = get_string('modulename', 'statements');
$strTOC = get_string('TOC', 'statements');

$mode = 'statement';

if (isset($_GET['standing'])) {
    $mode = 'standing';
} else if (isset($_GET['submit'])) {
    $mode = 'submit';
}

if ($mode == 'standing') {
	$last_nav_name = get_string('standing','statements');
} else {
	$last_nav_name = $chapter->name;
}


/// prepare header
if (!$without_course && $course->category) {
    $navigation = '<a href="../../course/view.php?id='.$course->id.'">'.$course->shortname.'</a>'."->".$statements->name;
} else {
    $navigation = '';
}

require('toc.php');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js(new moodle_url("js/amdjs-jquery.pagination/src/jquery.pagination.js"));
$PAGE->requires->js(new moodle_url("js/jquery.tmpl.js"));
$PAGE->requires->js(new moodle_url("js/handlebars.js"));
$PAGE->requires->js(new moodle_url("js/ajaxupload.js"));
$PAGE->requires->js(new moodle_url("js/map.js"));
$PAGE->requires->js(new moodle_url("js/module.js"));

if (!$without_course)
{
	$PAGE->set_url('/mod/statements/view.php', array('id' => $cm->id, 'chapterid' => $chapter->id));
	$PAGE->set_heading("$course->shortname: $statements->name"); // Required
	$PAGE->set_title($course->fullname);
	$PAGE->set_cacheable(false);
        statement_add_limits($chapter);
	statements_add_menu_block($chapter, $cm->id);
        if ($mode == 'standing' || $mode == 'submit') {
	    statements_add_group_selector_block($chapter, $statements, $cm->id);           
        } else {
	    statements_add_toc_block($chapters_new, $chapter, $statements, $cm, $edit);
        }
	echo $OUTPUT->header();
        if ($mode != 'submit' && $mode != 'standing') { 
	    echo format_text($OUTPUT->heading('Задача №'.$chapter->id.". ".$chapter->name, 4), FORMAT_HTML, array("noclean" => true), $course->id);
        }
	if ($statements->summary) {
		 $statements->intro = $statements->summary;
		 echo $OUTPUT->box(format_module_intro('statements', $statements, $cm->id), 'generalbox', 'summary');
	}
} else {
        $PAGE->set_context(context_system::instance());
	$PAGE->set_pagelayout('standard');
	$PAGE->set_url('/mod/statements/view.php', array('chapterid' => $chapter->id));
        if (isset($course)) { 
           $iid = $course->id;
        } else {
           $iid = null;
        }
        $heading = format_text($OUTPUT->heading(get_string('problem', 'statements')." №".$chapter->id.". ".$chapter->name), FORMAT_HTML, array("noclean" => true), $iid);
	$PAGE->set_title($heading);
	$PAGE->set_cacheable(false);
        statement_add_limits($chapter);
	statements_add_menu_block($chapter);
    echo $OUTPUT->header();
    echo $heading;
}
/// prepare chapter navigation icons
$previd = null;
$nextid = null;
$count = 0;
foreach ($chapters_new as $ch) {
    $count++;
}
$found = 0;
foreach ($chapters_new as $ch) {
    if ($found) {
        $nextid= $ch->id;
        break;
    }
    if ($ch->id == $chapter->id) {
        $found = 1;
    }
    if (!$found) {
        $previd = $ch->id;
    }
}
if ($ch == current($chapters_new)) {
    $nextid = $ch->id;
}
$chnavigation = '';


if (!isset($statements->id))
{
    $st_id = -1;
} else {
    $st_id = $statements->id;
}

//CHECK if user take part in OLYMPIAD now and can't access any other problem

$in_other_olymp = 0;
if ($USER->id != 1) {
    $in_other_olymp = $DB->count_records_sql("SELECT COUNT(*) from mdl_olympiad,mdl_statements WHERE mdl_olympiad.user_id=".$USER->id." AND mdl_statements.id=mdl_olympiad.contest_id AND mdl_olympiad.contest_id <>".$st_id." AND timestart < ".time()." AND timestop > ".time()) > 0;
    $in_other_olymp = $in_other_olymp || (!isset($statements) && ($DB->count_records_sql("SELECT COUNT(*) from mdl_olympiad,mdl_statements WHERE mdl_olympiad.user_id=".$USER->id." AND mdl_olympiad.contest_id = mdl_statements.contest_id AND timestart < ".time()." AND timestop > ".time()) > 0));
}

$in_other_virtual_olymp = 0;
if ($USER->id != 1) {
    $virtual_id = $DB->get_record_sql("SELECT * from mdl_virtualcontest WHERE mdl_virtualcontest.user_id=".$USER->id." AND mdl_virtualcontest.statement_id <>".$st_id." AND mdl_virtualcontest.start < ".time()." AND (mdl_virtualcontest.start + duration) > ".time());
    $in_other_virtual_olymp = $DB->count_records_sql("SELECT count(*) from mdl_virtualcontest WHERE mdl_virtualcontest.user_id=".$USER->id." AND mdl_virtualcontest.statement_id <>".$st_id." AND mdl_virtualcontest.start < ".time()." AND (mdl_virtualcontest.start + duration) > ".time()) > 0;
    $in_other_virtual_olymp = $in_other_virtual_olymp || (!isset($statements) && ($DB->count_records_sql("SELECT count(*) from mdl_virtualcontest WHERE mdl_virtualcontest.user_id=".$USER->id." AND mdl_virtualcontest.start < ".time()." AND (mdl_virtualcontest.start + duration) > ".time()) > 0));
}

if(!has_capability('mod/statements:authteacher', context_system::instance()) && ($in_other_olymp /*|| $in_other_virtual_olymp*/)) {
		echo "<div align='center'>В данный момент вы участвуете в олимпиаде и не имеете доступа к другим задачам сайта.</div>";
		if ($in_other_virtual_olymp) {
		    $c_other = get_coursemodule_from_instance('statements', $virtual_id->statement_id);
		}
		exit;
}
//CONTEST is OLYMPIAD
$olympiad = -1; //not Olympiad
if(!$without_course && $statements->olympiad == 1) {
	if ($participate = ($DB->count_records_sql("SELECT COUNT(*) from mdl_olympiad WHERE user_id=".$USER->id." AND contest_id=".$statements->id) > 0) || $allowedit) {
		if($statements->timestart > time()) {
		//BEFORE OLYMPIAD
			$olympiad = 0; //before Olympiad
            echo print_timer($statements->timestart, time(), 'До начала олимпиады осталось');
            echo '<span id="counter" align="center"> </span>';
			if(!$allowedit) {
				exit;
			}
		}
		else {
			if($statements->timestop < time()) {
				//AFTER OLYMPIAD
				$olympiad = 2; //after Olympiad
				$statements->customtitles = false;
                echo '<div align="center">Олимпиада завершена. Режим дорешивания.</div>';
			} else {
			//DURING OLYMPIAD
                if(!$allowedit) {
                    $statements->customtitles = true;				
                }
//                $olympiad = 2; //during Olympiad
                echo print_timer($statements->timestop, time(), 'До окончания олимпиады осталось');
            }
		}
	}
	else 
    {
		if ($register == 1) {
            $db->Execute('INSERT INTO mdl_olympiad(user_id, contest_id) VALUES ('.$USER->id.','.$statements->id.')');
            $db->Execute('INSERT INTO mdl_virtualcontest(user_id, statement_id, start, duration) VALUES ('.$USER->id.','.$statements->id.', '.$statements->timestart.','.($statements->timestop - $statements->timestart).')');            
			redirect('view.php?id='.$cm->id);
		}
		else {
			notice_yesno('Данный контест доступен только в режиме олимпиады. В случае участия в ней, на время проведения олимпиады у вас не будет доступа к другим задачам на сайте. Прервать свое участие до окончания олимпиады нельзя. Вы по-прежнему хотите принять участие в олимпиаде?','view.php?id='.$cm->id.'&register=1','/moodle/');
			exit;
		}
	}
} else if ($end_olymp) {
    $db->Execute('UPDATE mdl_virtualcontest SET duration = '.time().' - mdl_virtualcontest.start WHERE user_id ='.$USER->id.' AND statement_id = '.$statements->id);
    redirect('view.php?id='.$cm->id);
}
//END of OLYMPIAD parsing
if(!$without_course) {
    if ($previd) {
        $chnavigation .= '<a title="'.get_string('navprev', 'statements').'" href="view.php?id='.$cm->id.'&amp;chapterid='.$previd.'"><img src="pix/nav_prev.gif" class="bigicon" alt="'.get_string('navprev', 'statements').'"/></a>';
    } else {
        $chnavigation .= '<img src="pix/nav_prev_dis.gif" class="bigicon" alt="" />';
    }
    if ($nextid) {
        $chnavigation .= '<a title="'.get_string('navnext', 'statements').'" href="view.php?id='.$cm->id.'&amp;chapterid='.$nextid.'"><img src="pix/nav_next.gif" class="bigicon" alt="'.get_string('navnext', 'statements').'" /></a>';
    }
}
/// prepare print icons
if ($without_course || $statements->disableprinting) {
    $printstatements = '';
    $printchapter = '';
} else {
    $printstatements = '<a title="'.get_string('printstatements', 'statements').'" href="print3.php?id='.$cm->id.'" onclick="this.target=\'_blank\'"><img src="pix/print_statements.gif" class="bigicon" alt="'.get_string('printstatements', 'statements').'"/></a>';
    $printchapter = '<a title="'.get_string('printchapter', 'statements').'" href="print3.php?id='.$cm->id.'&amp;chapterid='.$problem_id.'" onclick="this.target=\'_blank\'"><img src="pix/print_chapter.gif" class="bigicon" alt="'.get_string('printchapter', 'statements').'"/></a>';
}

$groups = $DB->get_records_sql("SELECT distinct mdl_ejudge_group.id, mdl_ejudge_group.name ".
					 "FROM mdl_ejudge_group left join mdl_ejudge_group_users on  mdl_ejudge_group.id = mdl_ejudge_group_users.group_id ".
					 "WHERE mdl_ejudge_group.visible=1 ".
					 " AND (mdl_ejudge_group_users.user_id=".$USER->id." OR  mdl_ejudge_group.owner_id=".$USER->id.")");

if ($edit) {
    $tocwidth = $CFG->statements_tocwidth + 80;
} else {
    $tocwidth = $CFG->statements_tocwidth;
}

$doimport = ($allowedit and $edit) ? '<a href="import.php?id='.$cm->id.'">'.get_string('doimport', 'statements').'</a>' : '';

// =====================================================
// statements display HTML code
// =====================================================
$vcontest = 0;
if ($count > 0) {
    if($usertype && $usertype->data==="Команда" || (!$without_course && $statements->virtual_olympiad)) {
        $vcontest=$DB->get_record_sql("SELECT start,duration FROM mdl_virtualcontest where user_id=".$USER->id.
                           " AND statement_id=".$statements->id);
        if(!$vcontest) {
            if(!$_POST["start"]) {
                echo '<form method="POST" action="view.php?id='.$cm->id.'"><input type="hidden" name="start" value="1"><input type="submit" value="Начать виртуальный турнир">';
            } else {
                $db->Execute('INSERT INTO mdl_virtualcontest VALUES ("",'.$USER->id.','.$statements->id.','.time().','.$statements->virtual_duration.')');   
                $vcontest=$DB->get_record_sql("SELECT start,duration FROM mdl_virtualcontest where user_id=".$USER->id.
                           " AND statement_id=".$statements->id);
            }
        } else {
            if ($vcontest->start + $vcontest->duration > time()) {
                echo '<form method="GET" action="view.php?id='.$cm->id.'"><input type="hidden" name="end_olymp" value="1"><input type="hidden" value="'.$cm->id.'" name="id"><input type="submit" value="Закончить виртуальный турнир">';
            }
        }
        
        if ($vcontest) {echo '
<script type="text/javascript" language="javascript">

	function runMultiple()
	{
		if (time>0) {		
			time -= 1;
			var h = Math.floor(time/3600);
			var m = Math.floor((time-Math.floor(time/3600)*3600)/60);
			m = m>9?m:"0"+m;
			var s = time%60;
			s = s>9?s:"0"+s;
			document.getElementById("counter").innerHTML = "<font size=+1><b>ОСТАЛОСЬ<br>"+h+":"+m+":"+s+"</b></font>";		
		
		} else {
			window.clearTimeout(timer);		
			document.getElementById("counter").innerHTML = "<p><font size=+1><b>ДОРЕШИВАНИЕ</b></font></p>";		
		}
	}
	var time='.($vcontest->duration-(time()-$vcontest->start)).';
	var timer = window.setInterval("runMultiple();", 1000);
</script>
';}} 
$show_statements =!$usertype || !isset($statements) || (($usertype->data!=="Команда") && $statements->virtual_olympiad == 0) || $vcontest;

if ($show_statements) {
    if (has_capability('moodle/site:edit_problem', context_system::instance())) {
    echo "<div id='statement_panel' class='bootstrap'>
    <div id='source_tree_div' class='bootstrap' style='display: none;'>
            <div id='source_tree_div_head'>
                <div id='source_tree_div_head_close' class='source_tree_div_head_button'
                    onClick='make_contest_cancel();' 
                    style='right: 5px;'>
                    Закрыть
                </div>
		<div id='source_tree_status' style='position: absolute; top: 20px;'>
		</div>
            </div>
            <div id='source_tree_div_body' style='position: absolute; left: 0px; right: 0px;
                bottom: 0px; top: 40px; overflow: scroll;'>
            </div>
        </div></div>
    ";
}
}
	if($show_statements) {
        $content = "";
        if (!$usertype || !isset($statements) || (!$statements->customtitles && $usertype->data!=="Команда") || isadmin()) {
        
               if(isadmin()) {
                    $content.= " :: <a href=\"#\" id=\"invert_limits\">Показать/спрятать лимиты</a>";
           	
                }
        }
		
        if($chapter->analysis) {
		$content .=   "<div id='analysis' style='display: none'><h1> </h1>".
 				$chapter->analysis . "</div>";
	}
        if($chapter->description) {
		$content .=   "<div id='description' style='display: none'>".
			$chapter->description . "</div>";
		}

		if (isadmin()) {
            if ($chapter->show_limits) {
            	$limit_action = 'hide';
            } else {
                $limit_action = 'show';
            }
		} else {
			$limit_action = 'null';
		}		

		if (isset($statements->id)) {
			$statement_id = $statements->id;
		} else {
			$statement_id = 'null';
		}	
		$content .= "<div id='problem_data' style='display: none' limit_action='".$limit_action."' problem_id='".$problem_id."' sample_tests='".$chapter->sample_tests."' statement_id='".$statement_id."'></div>";
        if(isset($_GET['ideal']))
            $content .= "<div id='ideal-solutions'></div>";  
        else
		    $content .= "<div id='ideal-solutions' style='display:none;'></div>";    
		$content .= "<div id='hint-list' style='display:none;'></div>";
	$content .='<script src="https://www.google.com/recaptcha/api.js"></script>';
    $content .='<script type="text/javascript" src="/mod/statements/lib/prism/prism.js"></script>';
    $content .='<link type="text/css" href="/mod/statements/lib/prism/prism.css" rel="stylesheet" />';
    $limit_bl = limit_block($chapter);
    $lang_time_bl = lang_time_block($problem_id);
    
    if (!$chapter->output_only) {
        $header_bl = "<table border='0' width='100%'><tr><td>".$limit_bl."</td><td>".$lang_time_bl."</td></tr></table>";
    } else {
        $header_bl = "";
    }
	$content .= $chapter->content;
    $content .= $chapter->sample_tests_html;
    if (has_capability('moodle/site:edit_problem', context_system::instance())) {
	    $content .= "<div id='problem_panel' class='bootstrap'> 
    	<button type='button' id='problem_tests_load' class='btn btn-light'>Показать тесты</button> 
	    <button type='button' id='problem_tests_stuse' class='btn btn-light hide'>Использовать в условии</button> 
	    <button type='button' id='problem_generate_samples' class='btn btn-light'>Сгенерировать примеры</button> 
	    <a data-toggle='modal' href='#myModal' class='btn btn-light'>Добавить тест</a>
	    <div id='myAlert'></div>
	    <div id='problem_tests' style='display:none' class='bootstrap'></div>  
	    </div>";
    }
    
		$PrototypeLoaded = true;
		if ($USER->id <= 1) {			
				$string['loginreq'] = ' для сдачи задач необходимо  <a  href="/login/">войти</a> в систему ';
			
				$content .= "<div id='submit' class='statements_chapter_title' style='margin-top: 10px;font-size:11pt'>".get_string('submit_linktext','statements').": ".$string['loginreq']."</div>";	
		} else {
			require_once("./submit_form.php");
			$content .= get_submit_form($chapter);
			require_once("./submits.php");
     			$submits = new Submits($USER);
			$submits->setUserId($USER->id);
			if (isset($run_id)) {
			    $submits->setRunIdForShow($run_id);
			}
			$submits->setProblemId($problem_id);
			$content .= "<div class='submit_box' align='center' width='100%' height='100%'>".$submits->getAJAXTable()."</div>";			
		}
	
	
		if ($mode=='submit') {
                        if (isset($statements)) {
			    $statement_id = $statements->id;
                        }
		        $group_id = 0;
                        if (array_key_exists('group_id', $_GET)) {
			    $group_id = (int)$_GET['group_id'];
                        }

			if (isset($_GET['from_timestamp'])) {
				$from_timestamp = (int)$_GET['from_timestamp'];
			} else {
				$from_timestamp = -1;
			}

                        if (isset($_GET['to_timestamp'])) {
				$to_timestamp = (int)$_GET['to_timestamp'];
			} else {
				$to_timestamp = -1;
			}
	
			if (isset($_GET['lang_id'])) {
				$lang_id = (int)$_GET['lang_id'];
			} else {
				$lang_id = -1;
			}
			
			if (isset($_GET['status_id'])) {
				$status_id = (int)$_GET['status_id'];
			} else {
				$status_id = -1;
			}	
			
			$user_id = $USER->id;

			require_once("./submits.php");
			$submits = new Submits($USER);
                        if (isset($statements)) {
			     $submits->setStatementId($statements->id);
                        }
			$submits->setLangId($lang_id);
			$submits->setStatusId($status_id);

			if (!isset($statements) || !$statements->id) {
				$submits->setProblemId($problem_id);
			}
			
			if ($group_id) {
				$submits->setGroupId($group_id);			
			}

                        if ($from_timestamp) {
				$submits->setFromTimestamp($from_timestamp);			
			}

        		if ($to_timestamp) {
				$submits->setToTimestamp($to_timestamp);			
			}

			if ($user_id_for_submits) {
				$submits->setUserId($user_id_for_submits);			
			}

			if (isset($_GET['count'])) {
				$submits->base=(int)($_GET['count']);
			}
			$PrototypeLoaded = false;
			$content = "<div class='submit_box' align='center' width='100%' height='100%'>".$submits->getAJAXTable()."</div>";			
		} else if ($mode == 'standing') {
            //echo $olympiad;
            if ($olympiad != 1 || has_capability('mod/statements:authteacher', context_system::instance())) {
                if (isset($_GET['virtual'])) {
                  $virtual = true;
                  $statements->current_duration = time() - $vcontest->start;
                  $statements->duration = $vcontest->duration;
                  if ( $statements->current_duration >  $statements->duration) {
                     $statements->current_duration = $statements->duration;
                  }
                  if ($USER->capabilities[1]['moodle/ejudge_monitor:admin']!="1" && !has_capability('mod/statements:authteacher', context_system::instance()) && $statements->current_duration < $statements->duration && 
                    $statements->current_duration > $statements->duration - 3600 ) {
                      $statements->current_duration = $statements->duration - 3600;
                  }
                  if (($USER->capabilities[1]['moodle/ejudge_monitor:admin']=="1" || has_capability('mod/statements:authteacher', context_system::instance())) && $statements->duration==0 ) {
                     $statements->current_duration = 1800000;
                  }
                } 
            else {
                  $virtual = false;
                 }
		 if (isset($statements->id)) {
                 	$statement_id = $statements->id;
		 }
		 $group_id = 0;
                 if (array_key_exists("group_id", $_GET)) {
                     $group_id = (int)$_GET['group_id'];
		 }
                 $user_id = $USER->id;
		 $content = "<div align='center' width='100%'><div id='MonitorResult'><div class='d-flex justify-content-center'><div class='spinner-border text-primary' role='status'><span class='sr-only'>Loading...</span></div></div></div></div>";
			
	} else {
                 $content = "";
      	}
     }
        

        if (has_capability('moodle/site:edit_problem', context_system::instance())) {
           $content .= " 
<div id='dmb' class='bootstrap' height='100px' width='100px'>
            <script type='text/javascript' src='/py-source/js/jsrender.js'></script>
          <div id='myModal' class='modal hide' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
            <div class='modal-header'>
              <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
              <p id='myModalLabel'>Добавление теста</p>
            </div>
            <div class='modal-body'>
               <form class='well'>
                  <div class='control-group'>
                    <label class='control-label' for='input_data'>Тест</label>
                    <div class='controls'>
                        <textarea class='input-xlarge' id='input_data' rows='5'></textarea>
                    </div>
                  </div>
                  <div class='control-group'>
                    <label class='control-label' for='output_data'>Ответ</label>
                    <div class='controls'>
                        <textarea class='input-xlarge' id='output_data' rows='5'></textarea>
                    </div>
                  </div>
               </form>
            </div>
            <div class='modal-footer'>
              <button class='btn' data-dismiss='modal'>Закрыть</button>
              <button class='btn btn-primary add_test_button'>Добавить</button>
            </div>
          </div>
</div> ";
        }

		$hidden = $chapter->hidden ? ' dimmed_text' : null;
        echo $OUTPUT->box_start('generalbox book_content' . $hidden);
        echo format_text($content, FORMAT_HTML, array('noclean'=>true));
		echo $OUTPUT->box_end();
	}
} else {echo("No problems in this section yet!");}
  
?>
<script id="solutions-template" type="text/x-jquery-tmpl">
  {%each solutions%}
  <tr>
    <td>${id}</td>
    <td>${status}</td>
    <td>${lang}</td>
    <td>${cpu_time_limit} s</td>
    <td>${real_time_limit} s</td>
    <td>${memory_limit} MB</td>
    <td>${cputime} ms</td>
    <td>${realtime} ms</td>
    <td>${vmsize} B</td>
    <td><a href="/get-solution/${id}/main.${extension}">Source</a></td>
    <td><a href="/get-solution/${id}/stdin.txt">Stdin</a></td>
    <td><a href="/get-solution/${id}/r_stdout.txt">Stdout</a></td>
    <td><a href="/get-solution/${id}/r_stderr.txt">Stderr</a></td>
    <td><a href="/get-solution/${id}/c_stdout.txt">Stdout</a></td>
    <td><a href="/get-solution/${id}/c_stderr.txt">Stderr</a></td>
  </tr>
  {%/each%}
</script>
<?php
    $group_id = 0;
    if (array_key_exists("group_id", $_GET)) {
    	$group_id = (int)$_GET['group_id'];
    }
    $user_id = $USER->id;
    $group_len = count($groups);
    $groups_html = '<script id="groups_data" type="application/json">[';
    $idx = 0;
    foreach ($groups as $group) {
         $groups_html.= '{"id": '.$group->id.', "name": '.json_encode(htmlspecialchars($group->name)).'}';
         if ($idx != $group_len - 1) {
             $groups_html .= ',';
         }
         $idx += 1;
    }
    $groups_html .= ']</script>';
    echo $groups_html;
    if ($mode == 'standing') {
        $content = "
		<div id=\"group_id\" style=\"display:none\">".$group_id."</div>
                <div id=\"statement_id\" style=\"display:none\">".$cm->id."</div>
		<div id=\"monitor_course_id\" style=\"display:none\">".$course->id."</div>
        ";
        echo format_text($content, FORMAT_HTML, array("noclean" => true), $course->id);
    }
echo "<div id=\"statement_mode\"  style=\"display:none\">".$mode."</div>";
echo $OUTPUT->footer();
?>


