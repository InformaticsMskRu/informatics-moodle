<?php
require_once($CFG->dirroot.'/mod/statements/lib.php');
require_once('moodleform_mod.php');

class mod_monitorgroup_mod_form extends moodleform_mod {

    function definition() {
		
		$monitors = get_records_menu("monitors",'','','name','id, name');
	
        global $CFG;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', '');

        $mform->addElement('select', 'monitor_id', get_string('monitor_id','monitor'), 
			$monitors
		);
		
		
        $mform->setType('name', PARAM_TEXT);
//        $mform->addRule('monitor_id', null, 'required', null, 'client');
        $mform->setDefault('monitor_id', 0);

//        $mform->addElement('htmleditor', 'summary', get_string('summary'));
//        $mform->setType('summary', PARAM_RAW);
//        $mform->addRule('summary', null, null, null, 'client');

//        $mform->addElement('select', 'numbering', get_string('numbering', 'contest'), contest_get_id_name());
//        $mform->setDefault('numbering', 0);

        $this->standard_coursemodule_elements();

//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }


}
?>