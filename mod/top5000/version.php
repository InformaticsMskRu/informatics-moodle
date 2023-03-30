<?php // $Id: version.php,v 1.3 2006/08/28 16:41:20 mark-nielsen Exp $
/**
 * Code fragment to define the version of NEWMODULE
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @author 
 * @version $Id: version.php,v 1.3 2006/08/28 16:41:20 mark-nielsen Exp $
 * @package NEWMODULE
 **/

$plugin->version  = 2020061500;  // The current module version (Date: YYYYMMDDXX)
$plugin->requires = 2020061500;
$plugin->cron     = 0;           // Period for cron to check this module (secs)
$plugin->component = "mod_top5000";
?>
