<?PHP // $Id: version.php,v 1.1.8.2 2007/05/20 06:02:00 skodak Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$plugin->version  = 2020061600;  // The current module version (Date: YYYYMMDDXX)
$plugin->requires = 2020061500;  // Requires this Moodle version
$plugin->cron     = 0;           // Period for cron to check this module (secs)
$plugin->component = 'mod_statements';

$release = "1.4alpha";          // User-friendly version number

?>
