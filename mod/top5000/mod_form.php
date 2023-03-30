<?php
require_once('moodleform_mod.php');

class mod_top5000_mod_form extends moodleform_mod {

    function definition() {
		
        global $CFG;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', '');
        $this->standard_coursemodule_elements();

//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }


}
?>