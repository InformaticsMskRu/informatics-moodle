<?PHP // $Id: toc.php,v 1.2.8.1 2007/05/20 06:02:02 skodak Exp $

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
 
/// included from mod/statements/view.php and print.php
///
/// uses:
///   $chapters - all statements chapters
///   $chapter - may be false
///   $cm - course module
///   $statements - statements
///   $edit - force editing view

function statement_add_limits($chapter) {
    global $PAGE, $DB;
    $bc = new block_contents();
    $bc->title = "Служебное";
    $bc->attributes['class'] = 'block block_statements_menu';

    $bc_limit = new block_contents();
    $bc_limit->title = 'Ограничения';
    $bc_limit->attributes['class'] = 'block block_statements_menu';
    $bc_limit->content = limit_block($chapter); 
    if (strlen($bc_limit->content) > 0) {
        $PAGE->blocks->add_fake_block($bc_limit, $PAGE->blocks->get_default_region());
    }

    if (has_capability('moodle/site:edit_problem', context_system::instance()) && isset($chapter)) {
        $query='SELECT 1,ejudge_id,mdl_ejudge_contest.name as n,mdl_ejudge_problem.short_id as letter from mdl_problems, mdl_ejudge_contest, mdl_ejudge_problem where mdl_problems.pr_id=mdl_ejudge_problem.id AND mdl_ejudge_contest.id=mdl_ejudge_problem.contest_id AND mdl_problems.id='.$chapter->id;
        $cont=$DB->get_records_sql($query);
        $string = (int)($cont[1]->ejudge_id).'/'.$cont[1]->letter;
        $url = new moodle_url("/cgi-bin/new-master", array("contest_id"=>$cont[1]->ejudge_id));
        $bc->content = $cont[1]->n." <a href='".$url."'>".$string."</a>";
        $PAGE->blocks->add_fake_block($bc, $PAGE->blocks->get_default_region());
    }
}


function statements_add_menu_block($chapter, $cmid=0) {
    global $PAGE, $USER, $DB, $CFG, $mode;
    $content = "";
    if ($cmid) {
        if ($mode == 'statement') {
             $content .= "<div> <a href='view.php?chapterid=".$chapter->id."&submit'>Посылки по задаче</a></div>";
        }
        $content .= "<div> <a href='view.php?id=".$cmid."&submit'>Все посылки</a></div>";
        $content .= "<div> <a href='view.php?id=".$cmid."&standing'>Результаты</a></div>"; 
    } else {
        $content .= "<div> <a href='view.php?chapterid=".$chapter->id."&submit'>Посылки</a></div>";
    }
    $bc = new block_contents();
    $bc->title = "";
    $bc->attributes['class'] = 'block block_statements_menu';
    $bc->content = $content;
    $PAGE->blocks->add_fake_block($bc, $PAGE->blocks->get_default_region());
}

function statements_add_group_selector_block($chapter, $statements, $cmid=0) {
    global $PAGE, $USER, $DB, $CFG;
    $content = "";
    $content .= "<div id='groups_nav' class='list-group'></div>";

     $content .= '<div id="MonitorResultLoadingTpl" style="display: none">
			<div class="spinner-border text-primary" role="status">
	    		<span class="sr-only">Loading...</span>
	 		</div>
		</div>';
    $bc = new block_contents();
    $bc->title = "Список групп";
    $bc->attributes['class'] = 'block block_statements_menu';
    $bc->content = $content;
    $PAGE->blocks->add_fake_block($bc, $PAGE->blocks->get_default_region());
}

function statements_add_toc_block($chapters, $chapter, $statements, $cm, $edit = null) {
	global $PAGE, $USER;
	if ($edit === null) {
		if (has_capability('mod/statements:edit', context_module::instance($cm->id))) {
			if (isset($USER->editing)) {
				$edit = $USER->editing;
			} else {
				$edit = 0;
			}
		} else {
			$edit = 0;
		}
	}

	$bc = new block_contents();
	$bc->title = get_string('toc', 'mod_statements');
	$bc->attributes['class'] = 'block block_statements_toc';
	$bc->content = statements_get_toc($chapters, $chapter, $statements, $cm, $edit);
	$defaultregion = $PAGE->blocks->get_default_region();
	$PAGE->blocks->add_fake_block($bc, $defaultregion);
}

function statements_get_toc($chapters, $chapter, $book, $cm, $edit) {
	global $USER, $OUTPUT;
	for ($i=1; $i<=26; $i++)	{
        $numeration['alphabetically'][$i] = chr(ord('A')+$i-1);
        $numeration['alphabetically'][$i+26] = 'A'.chr(ord('A')+$i-1);
        $numeration['alphabetically'][$i+52] = 'B'.chr(ord('A')+$i-1);
        $numeration['alphabetically'][$i+78] = 'C'.chr(ord('A')+$i-1);
	}

	$toc = '';          //representation of toc (HTML)

	$context = context_module::instance($cm->id);
	$nch = 0; //chapter number
	$ns = 0;  //subchapter number
	$title = '';
	$first = 1;

	$toc .= html_writer::start_tag('div', array('class' => 'statements_toc statements_toc_alpha clearfix'));
	$toc .= html_writer::start_tag('ul');
	$i = 0;
	foreach($chapters as $ch) {
		$i++;
		$title = trim(strip_tags($ch->name));
		$titleunescaped = trim(format_string($ch->name, true, array('context' => $context, 'escape' => false)));
	    $toc .= html_writer::start_tag('li');
	    if (!$ch->cur_hidden) {
        	$nch++;
			$ns = 0;
			$title = "<b>".$numeration['alphabetically'][$nch].".</b> $title";
			$titleout = $title;
        } else {
			$nch++;
            $title = "x $title";
            $titleout = html_writer::tag('span', $title, array('class' => 'dimmed_text'));
		}

		if ($ch->id == $chapter->id) {
			$toc .= html_writer::tag('strong', $titleout, array('class' => 'text-truncate'));
		} else {
			$toc .= html_writer::link(new moodle_url('view.php', array('id' => $cm->id, 'chapterid' => $ch->id)), $titleout,
				array('title' => $titleunescaped, 'class' => 'text-truncate')
		 	);
		}
		if (has_capability('moodle/site:edit_problem', context_system::instance())) {
			$toc .= html_writer::link(
				new moodle_url('edit.php', array('cmid' => $cm->id, 'chapterid' =>  $ch->id)),
				$OUTPUT->pix_icon('t/edit', get_string('editchapter', 'mod_statements', $title)),
				array('title' => get_string('editchapter', 'mod_statements', $titleunescaped))
			);
		}
		if ($edit) {		
			if ($i != 1) {
				$toc .= html_writer::link(new moodle_url('move.php', array('id' => $cm->id, 'chapterid' => $ch->id, 'up' => '1', 'sesskey' => $USER->sesskey)),
					$OUTPUT->pix_icon('t/up', get_string('movechapterup', 'mod_statements', $title)),
					array('title' => get_string('movechapterup', 'mod_statements', $titleunescaped)));
			}
        	if ($i != count($chapters)) {
 	$toc .= html_writer::link(new moodle_url('move.php', array('id' => $cm->id, 'chapterid' => $ch->id, 'up' => '0', 'sesskey' => $USER->sesskey)),
					$OUTPUT->pix_icon('t/down', get_string('movechapterdown', 'mod_statements', $title)),
					array('title' => get_string('movechapterdown', 'mod_statements', $titleunescaped)));
			}
			$deleteaction = new confirm_action(get_string('deletechapter', 'mod_statements', $titleunescaped));
			$toc .= $OUTPUT->action_icon(
				new moodle_url('delete.php', [
					'id'        => $cm->id,
					'chapterid' => $ch->id,
					'sesskey'   => sesskey(),
					'confirm'   => 1,
				]),
				new pix_icon('t/delete', get_string('deletechapter', 'mod_statements', $title)),
				$deleteaction,
				['title' => get_string('deletechapter', 'mod_statements', $titleunescaped)]
			);

			if ($ch->cur_hidden) {
				$show_action = 'show';
			} else {
				$show_action = 'hide';
			}

		    $toc .= html_writer::link(new moodle_url('show.php', array('id' => $cm->id, 'chapterid' => $ch->id, 'sesskey' => $USER->sesskey)),
				$OUTPUT->pix_icon('t/'.$show_action, get_string($show_action.'chapter', 'mod_statements', $title)),
				array('title' => get_string($show_action.'chapter', 'mod_statements', $titleunescaped)));
		}
		$toc .= html_writer::end_tag('li');
		$first = 0;
	}
	$toc .= html_writer::end_tag('ul');
	$toc .= html_writer::end_tag('div');
	return $toc;
} 

?>
