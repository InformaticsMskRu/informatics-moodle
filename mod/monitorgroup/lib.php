<?php  // $Id: lib.php,v 1.4 2006/08/28 16:41:20 mark-nielsen Exp $
/**
 * Library of functions and constants for module contest
 *
 * @author 
 * @version $Id: lib.php,v 1.4 2006/08/28 16:41:20 mark-nielsen Exp $
 * @package monitorgroup
 **/



$contest_CONSTANT = 7;     /// for example

function monitorgroup_get_id_name() {
    return array (0       => 'a',
                  1    => 'b',
                  2    => 'c',
                  3   => 'd' );
}


/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will create a new instance and return the id number 
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted contest record
 **/
function monitorgroup_add_instance($monitor) {
    
    $monitor->timemodified = time();

    # May have to add extra stuff in here #
    
    return insert_record("monitorgroup", $monitor);
}

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will update an existing instance with new data.
 *
 * @param object $instance An object from the form in mod.html
 * @return boolean Success/Fail
 **/
function monitorgroup_update_instance($monitor) {

    $monitor->timemodified = time();
    $monitor->id = $monitor->instance;

    # May have to add extra stuff in here #

    return update_record("monitorgroup", $monitor);
}

/**
 * Given an ID of an instance of this module, 
 * this function will permanently delete the instance 
 * and any data that depends on it. 
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 **/
function monitorgroup_delete_instance($id) {

    if (! $monitor = get_record("monitorgroup", "id", "$id")) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    if (! delete_records("monitorgroup", "id", "$monitor->id")) {
        $result = false;
    }

    return $result;
}

/**
 * Return a small object with summary information about what a 
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 **/
function monitorgroup_user_outline($course, $user, $mod, $monitor) {
    return $return;
}

/**
 * Print a detailed representation of what a user has done with 
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function monitorgroup_user_complete($course, $user, $mod, $monitor) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity 
 * that has occurred in contest activities and print it out. 
 * Return true if there was output, or false is there was none. 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function monitorgroup_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such 
 * as sending out mail, toggling flags etc ... 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function monitorgroup_cron () {
    global $CFG;

    return true;
}

/**
 * Must return an array of grades for a given instance of this module, 
 * indexed by user.  It also returns a maximum allowed grade.
 * 
 * Example:
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 *
 * @param int $contestid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 **/
function monitorgroup_grades($monitorid) {
   return NULL;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of contest. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $contestid ID of an instance of this module
 * @return mixed boolean/array of students
 **/
function monitorgroup_get_participants($monitorid) {
    return false;
}

function monitorgroup_get_types() {
    global $CFG;

    $types = array();

    $type = new object();
    $type->modclass = MOD_CLASS_RESOURCE;
    $type->type = 'monitorgroup';
    $type->typestr = get_string('modulename', 'monitorgroup');
    $types[] = $type;

    return $types;
}


/**
 * This function returns if a scale is being used by one contest
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $contestid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 **/
function monitorgroup_scale_used ($monitorid,$scaleid) {
    $return = false;

    //$rec = get_record("contest","id","$contestid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}
   
    return $return;
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other contest functions go here.  Each of them must have a name that 
/// starts with contest_


?>
