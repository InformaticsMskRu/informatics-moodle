<?php // $Id: mysql.php,v 1.3 2006/08/28 16:41:20 mark-nielsen Exp $
/**
 * Upgrade procedures for NEWMODULE
 *
 * @author 
 * @version $Id: mysql.php,v 1.3 2006/08/28 16:41:20 mark-nielsen Exp $
 * @package NEWMODULE
 **/

/**
 * This function does anything necessary to upgrade 
 * older versions to match current functionality 
 *
 * @uses $CFG
 * @param int $oldversion The prior version number
 * @return boolean Success/Failure
 **/
function top5000_upgrade($oldversion) {
    global $CFG;

    if ($oldversion < 2006042900) {

       # Do something ...

    }

    return true;
}

?>
