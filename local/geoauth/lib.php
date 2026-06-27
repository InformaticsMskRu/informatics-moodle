<?php
defined('MOODLE_INTERNAL') || die();

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
 * Called from auth/oauth2/classes/auth.php::loginpage_idp_list()
 * to filter out Google OAuth for blocked countries.
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
