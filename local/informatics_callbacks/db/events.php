<?php
$observers = array(
     array(
        'eventname' => '\core\event\user_created',
        'callback' => 'local_informatics_callbacks_post_signup_requests',
        'internal'=> false,
    )
);
?>
