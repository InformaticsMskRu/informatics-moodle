# local_geoauth

Enables password recovery and username/password login for users who originally signed up via Google OAuth2.

## Problem

By default Moodle sets `can_reset_password()` to false for oauth2 users, so the "Forgot password" form sends a vague info email with no reset link. Even if a reset link were sent, `user_update_password()` in the oauth2 plugin does nothing — no password hash is stored.

## What this plugin does

1. **Lets OAuth2 users receive a real password-reset email** — via a core patch that returns `true` from `can_reset_password()`.
2. **Saves the new password hash** — via a core patch that adds `user_update_password()` calling `update_internal_user_password()`.
3. **Switches `user.auth` to `manual` after the reset** — so the user can log in with username/password going forward. Done in `local_geoauth_post_set_password_requests()`, a standard Moodle callback, no core patch needed.

Google login continues to work because the oauth2 plugin looks users up via `auth_oauth2_linked_login` (keyed by Google account ID), not by `user.auth`.

## Requirements

- Moodle 3.9
- Core patch applied (see below)

## Core patch

File: `patches/auth_oauth2_password_recovery.patch`
Target: `auth/oauth2/classes/auth.php`

### Apply

```bash
cd /path/to/moodle
patch -p1 < /path/to/local/geoauth/patches/auth_oauth2_password_recovery.patch
```

### Reapply after Moodle upgrade

```bash
cd /path/to/moodle
patch -p1 < /path/to/local/geoauth/patches/auth_oauth2_password_recovery.patch
```

If the patch no longer applies cleanly, manually make two changes in `auth/oauth2/classes/auth.php`:

1. Change `can_reset_password()` to return `true`.
2. Add the method:
```php
public function user_update_password($user, $newpassword) {
    return update_internal_user_password($user, $newpassword);
}
```

## User flow after changes

1. User visits "Forgot password", enters their email.
2. Moodle sends a real password-reset link (not the vague info email).
3. User sets a new password — hash is stored in `user.password`.
4. Plugin callback switches `user.auth` from `oauth2` to `manual`.
5. User can now log in with username/password **or** Google (both work).
