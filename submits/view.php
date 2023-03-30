<?PHP 

    require_once("../config.php");
    $user_id      = optional_param('user_id',     0,      PARAM_INT);
    $group_id      = optional_param('group_id',     0,      PARAM_INT);
    $from_timestamp     = optional_param('from_timestamp',     -1,      PARAM_INT);
    $to_timestamp      = optional_param('to_timestamp',     -1,      PARAM_INT);
    $status_id      = optional_param('status_id',     -1,      PARAM_INT);
    $lang_id      = optional_param('lang_id',     -1,      PARAM_INT);
    $with_comment = optional_param('with_comment', 0, PARAM_BOOL);
    $with_unread_comment = optional_param('with_unread_comment', 0, PARAM_BOOL);

    $securitycontext = get_context_instance(CONTEXT_SYSTEM, SITEID);   // SYSTEM context

    # $fullname = get_string('submitsAllLastWeek');
    $fullname = 'Посылки';
 
    if ($user_id == 0 && $group_id == 0) {
        $user_id = $USER->id;
    }
 
    $PAGE->requires->jquery();
    $PAGE->requires->jquery_plugin('ui');
    $PAGE->requires->js(new moodle_url("js/amdjs-jquery.pagination/src/jquery.pagination.js"));
    $PAGE->requires->js(new moodle_url("js/jquery.tmpl.js"));
    $PAGE->requires->js(new moodle_url("js/handlebars.js"));
    $PAGE->requires->js(new moodle_url("js/ajaxupload.js"));
    $PAGE->requires->js(new moodle_url("js/map.js"));
    $PAGE->requires->js(new moodle_url("js/module.js"));
    
    $PAGE->set_url('/submitss/view.php', array(
        'user_id' => $user_id, 
        'group_id' => $group_id,
        'status_id' => $status_id,
        'lang_id' => $lang_id
    ));

    if ($user_id > 0) {
        $fullname = get_string('submitsUser');
        if ($user = $DB->get_record("user", array("id"=>$user_id))) {
            # get_string('submitsUser')
            $fullname = "Посылки пользователя: ".fullname($user, has_capability('moodle/site:viewfullnames', $securitycontext));
        }
    } else if ($group_id > 0){
        if ($group = $DB->get_record("ejudge_group", array("id"=>$group_id))) {
            # get_string('submitsGroup')
            $fullname = "Посылки группы: ".$group->name;            
        }
    }
   
    $PAGE->set_heading($fullname);
    $PAGE->set_title($fullname);
    $PAGE->set_cacheable(true);
    
    echo $OUTPUT->header();
    
    require_once("./../mod/statements/submits.php");
    $submits = new Submits($USER);
    $submits->setUserId($user_id);
    if ($user_id == 0) {
        $submits->setGroupId($group_id);
    }

    $submits->setStatusId($status_id);
    $submits->setLangId($lang_id);

    if ($with_comment) {
        $submits->setWithComment();
        if ($with_unread_comment) {
            $submits->setWithUnreadComment();
        }
    }

    echo "<div class='submit_box' align='center' width='100%' height='100%'>".$submits->getAJAXTable()."</div><div id='statement_mode' style='display:none'>submit</div>";
    echo $OUTPUT->footer();	

?>
