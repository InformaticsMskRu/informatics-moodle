# local_geoauth

Hides Google OAuth button on the Moodle login page for users from blocked countries (currently: RU), detected by IP via GeoIP2.

## How it works

The plugin filters the `loginpage_idp_list` result server-side so the Google button is never included in the rendered HTML.

Because Moodle 3.9 has no hook for filtering the IDP list from a local plugin, a small core patch is required.

## Requirements

- Moodle 3.9
- MaxMind GeoIP2 database (path configured in `$CFG->geoip2file`)
- Core patch applied (see below)

## Core patch

File: `patches/auth_oauth2_idp_filter_hook.patch`  
Target: `auth/oauth2/classes/auth.php`

### Apply

```bash
cd /path/to/moodle
patch -p1 < /path/to/local/geoauth/patches/auth_oauth2_idp_filter_hook.patch
```

### Reapply after Moodle upgrade

```bash
cd /path/to/moodle
patch -p1 < /path/to/local/geoauth/patches/auth_oauth2_idp_filter_hook.patch
```

If the patch no longer applies cleanly, manually add these 3 lines at the end of `loginpage_idp_list()` in `auth/oauth2/classes/auth.php`, just before `return $result`:

```php
$result = component_callback('local_geoauth', 'filter_idp_list', [$result], $result);
```

## Configuration

Blocked countries are defined in `lib.php`:

```php
const LOCAL_GEOAUTH_BLOCKED_COUNTRIES = ['RU'];
```

GeoIP2 database path is set in Moodle's `config.php`:

```php
$CFG->geoip2file = '/path/to/GeoLite2-City.mmdb';
```
