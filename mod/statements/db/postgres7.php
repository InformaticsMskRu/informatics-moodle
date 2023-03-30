<?PHP // $Id: postgres7.php,v 1.1 2006/03/12 18:40:01 skodak Exp $

function statements_upgrade($oldversion) {
// This function does anything necessary to upgrade
// older versions to match current functionality

    global $CFG;

    if ($oldversion < 2004060600) {
        execute_sql ("ALTER TABLE {$CFG->prefix}statements
                      CHANGE intro summary TEXT NOT NULL;
                     ");
    }
    if ($oldversion < 2004071100) {

        execute_sql ("ALTER TABLE {$CFG->prefix}statements_chapters
                      ADD importsrc VARCHAR(255);
                     ");
        execute_sql ("UPDATE {$CFG->prefix}statements_chapters
                      SET importsrc = '';
                     ");
        execute_sql ("ALTER TABLE {$CFG->prefix}statements_chapters
                      ALTER importsrc SET NOT NULL;
                     ");
        execute_sql ("ALTER TABLE {$CFG->prefix}statements_chapters
                      ALTER importsrc SET DEFAULT '';
                     ");
    }
    if ($oldversion < 2004071201) {
        execute_sql ("UPDATE {$CFG->prefix}log_display
                            SET action = 'print'
                            WHERE action = 'prINT';
                     ");
    }
    if ($oldversion < 2004081100) {
        execute_sql ("ALTER TABLE {$CFG->prefix}statements
                      ADD disableprinting INT2;
                     ");
        execute_sql ("UPDATE {$CFG->prefix}statements
                      SET disableprinting = '0';
                     ");
        execute_sql ("ALTER TABLE {$CFG->prefix}statements
                      ALTER disableprinting SET NOT NULL;
                     ");
        execute_sql ("ALTER TABLE {$CFG->prefix}statements
                      ALTER disableprinting SET DEFAULT '0';
                     ");
        execute_sql ("ALTER TABLE {$CFG->prefix}statements
                      ADD customtitles INT2;
                     ");
        execute_sql ("UPDATE {$CFG->prefix}statements
                      SET customtitles = '0';
                     ");
        execute_sql ("ALTER TABLE {$CFG->prefix}statements
                      ALTER customtitles SET NOT NULL;
                     ");
        execute_sql ("ALTER TABLE {$CFG->prefix}statements
                      ALTER customtitles SET DEFAULT '0';
                     ");
    }

    return true;
}

?>
