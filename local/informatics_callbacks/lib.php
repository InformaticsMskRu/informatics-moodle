<?php

function local_informatics_callbacks_post_signup_requests($data) {
    global $DB;
    debugging("local_informatics_callbacks_post_signup_requests 1", DEBUG_DEVELOPER);
    if (isset($data->eventname) && $data->eventname == '\core\event\user_created') {
        $userid = $data->objectid;
	debugging("local_informatics_callbacks_post_signup_requests userid $userid", DEBUG_DEVELOPER);
        $usertype_field = $DB->get_record('user_info_field', array('shortname'=>'usertype'));

	debugging("local_informatics_callbacks_post_signup_requests fieldid $usertype_field->id", DEBUG_DEVELOPER);

        $usertype = $DB->get_field('user_info_data', 'data', array('userid'=>$userid, 'fieldid'=>$usertype_field->id), IGNORE_MISSING);
	 debugging("local_informatics_callbacks_post_signup_requests usetype $usertype", DEBUG_DEVELOPER);
        if (!$data || $usertype != 'Преподаватель') {
            return;
        } 

        debugging("local_informatics_callbacks_post_signup_requests $data->objectid", DEBUG_DEVELOPER);
        $userid = $data->objectid;
        $context = context_system::instance();
        $role = $DB->get_record('role', array('shortname'=>'group'));
        role_assign($role->id, $userid, $context->id);
    }
}

?>
