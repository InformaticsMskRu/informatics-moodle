<?php
defined('MOODLE_INTERNAL') || die();

/**
 * After a password-reset flow completes for an oauth2 user, switch their auth
 * to 'manual' so they can log in with username/password from now on.
 * Google login continues to work via the linked-login table (auth_oauth2_linked_login),
 * which is keyed by Google account ID, not by user.auth.
 *
 * Called by core_login_post_set_password_requests() in login/lib.php.
 */
function local_geoauth_post_set_password_requests($data, $user) {
    global $DB;
    if ($user->auth === 'oauth2') {
        $DB->set_field('user', 'auth', 'manual', ['id' => $user->id]);
    }
}
