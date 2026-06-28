<?php
defined('MOODLE_INTERNAL') || die();

// --- Geo-based IDP filtering ---

const LOCAL_GEOAUTH_BLOCKED_COUNTRIES = ['RU'];

function local_geoauth_country_by_ip(string $ip): string {
    global $CFG;

    if (empty($CFG->geoip2file) || !file_exists($CFG->geoip2file)) {
        return '';
    }

    try {
        $reader = new GeoIp2\Database\Reader($CFG->geoip2file);
        $record = $reader->city($ip);
        return strtoupper($record->country->isoCode ?? '');
    } catch (\Exception $e) {
        return '';
    }
}

function local_geoauth_should_hide_google(): bool {
    global $USER;

    $ip = getremoteaddr();
    $country = local_geoauth_country_by_ip($ip);
    if (!empty($country) && in_array($country, LOCAL_GEOAUTH_BLOCKED_COUNTRIES)) {
        return true;
    }

    if (isloggedin() && !isguestuser()) {
        if (!empty($USER->country) && in_array(strtoupper($USER->country), LOCAL_GEOAUTH_BLOCKED_COUNTRIES)) {
            return true;
        }
    }

    return false;
}

/**
 * Called from auth/oauth2/classes/auth.php::loginpage_idp_list() via component_callback().
 * Filters out Google OAuth for blocked countries server-side.
 */
function local_geoauth_filter_idp_list(array $idps): array {
    if (!local_geoauth_should_hide_google()) {
        return $idps;
    }

    return array_values(array_filter($idps, function($idp) {
        $name = strtolower($idp['name'] ?? '');
        $url  = (string)($idp['url'] ?? '');
        return strpos($name, 'google') === false && strpos($url, 'google') === false;
    }));
}

// --- OAuth2 password recovery ---

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
