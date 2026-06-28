# local_geoauth

Moodle 3.9+ local plugin with two features:

1. **Geo-based IDP filtering** — hides the Google OAuth button on the login page for users from blocked countries (currently: RU), detected by IP via GeoIP2.
2. **OAuth2 password recovery** — lets users who signed up via Google OAuth request a password reset and log in with username/password afterwards.

Both features require small core patches to `auth/oauth2/classes/auth.php` (see below).

---

## Feature 1: Geo-based IDP filtering

### How it works

`local_geoauth_filter_idp_list()` is called server-side from `loginpage_idp_list()` via `component_callback()`. The Google button is never included in the rendered HTML for blocked-country IPs — nothing to bypass client-side.

### Requirements

- MaxMind GeoIP2 database, path set in `config.php`:
  ```php
  $CFG->geoip2file = '/path/to/GeoLite2-City.mmdb';
  ```

### Configuration

Blocked countries are defined in `lib.php`:
```php
const LOCAL_GEOAUTH_BLOCKED_COUNTRIES = ['RU'];
```

### Core patch

File: `patches/auth_oauth2_idp_filter_hook.patch`

```bash
cd /path/to/moodle
patch -p1 < /path/to/local/geoauth/patches/auth_oauth2_idp_filter_hook.patch
```

If the patch no longer applies cleanly, add this line at the end of `loginpage_idp_list()` in `auth/oauth2/classes/auth.php`, just before `return $result`:

```php
$result = component_callback('local_geoauth', 'filter_idp_list', [$result], $result);
```

---

## Feature 2: OAuth2 password recovery

### Problem

By default Moodle's oauth2 plugin returns `false` from `can_reset_password()`, so OAuth2 users get a vague "passwords cannot be reset" email with no reset link (tracked as [MDL-59298](https://tracker.moodle.org/browse/MDL-59298), never properly fixed upstream). Even if a link were sent, `user_update_password()` is a no-op in the oauth2 plugin.

### How it works

1. Core patch sets `can_reset_password()` → `true` and adds `user_update_password()` to store a bcrypt hash.
2. `local_geoauth_post_set_password_requests()` callback switches `user.auth` from `oauth2` to `manual` after reset.
3. Google login continues to work via `auth_oauth2_linked_login` (keyed by Google account ID, not `user.auth`).

### User flow

1. User visits "Forgot password", enters email.
2. Moodle sends a real reset link.
3. User sets a new password — hash stored in `user.password`.
4. Plugin switches `user.auth` to `manual`.
5. User can log in with **username/password or Google** (both work).

### Core patch

File: `patches/auth_oauth2_password_recovery.patch`

```bash
cd /path/to/moodle
patch -p1 < /path/to/local/geoauth/patches/auth_oauth2_password_recovery.patch
```

If the patch no longer applies cleanly, make two changes in `auth/oauth2/classes/auth.php`:

1. Change `can_reset_password()` to return `true`.
2. Add:
```php
public function user_update_password($user, $newpassword) {
    return update_internal_user_password($user, $newpassword);
}
```

---

## Applying all patches at once

```bash
cd /path/to/moodle
patch -p1 < /path/to/local/geoauth/patches/auth_oauth2_idp_filter_hook.patch
patch -p1 < /path/to/local/geoauth/patches/auth_oauth2_password_recovery.patch
```
