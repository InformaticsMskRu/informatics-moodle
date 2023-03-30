	<?php
require_once($CFG->dirroot.'/mod/statements/lib.php');
require_once('moodleform_mod.php');

class mod_statements_mod_form extends moodleform_mod {

    function definition() {

        global $CFG, $USER;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setDefault('name', 'Problems');

        $mform->addElement('editor', 'summary', get_string('summary'));
        $mform->setType('summary', PARAM_RAW);
//        $mform->addRule('summary', null, null, null, 'client');

        $mform->addElement('select', 'numbering', get_string('numbering', 'statements'), statements_get_numbering_types());
        $mform->setDefault('numbering', 1);
		if ($this->_instance)
		{
 			$mform->addElement('html','<br><a href="/py-source/source/dir/contest/new/'.$this->_instance.'-'.$this->_cm->id.'">Выбрать задачи из тематического рубрикатора</a>');
 			$mform->addElement('html','<br><a href="/legacy/add_probs_by_number.php?contest='.$this->_cm->id.'">Добавить задачи по id</a>');
			$mform->addElement('static', 'gridelem', '', '</div></div><iframe style="border:0px" width="1000" height="600"  src="/legacy/edit_problemlist.php?statement_id='.$this->_instance.'"></iframe><div><div>');		
		}

        $mform->addElement('checkbox', 'customtitles', get_string('customtitles', 'statements'));
        $mform->setDefault('customtitles', 0);
 //       $mform->addRule('customtitles', null, null, null, 'client');

        $mform->addElement('checkbox', 'olympiad', get_string('olympiad', 'statements'));
        $mform->setDefault('olympiad', 0);
 //       $mform->addRule('olympiad', null, null, null, 'client');


        $mform->addElement('date_time_selector', 'timestart', get_string('timestart', 'statements'), 
array(
    'startyear' => 1970, 
    'stopyear'  => 2020,
    'timezone'  => 99, 
    'applydst'  => true, 
    'step'      => 1
));
        $mform->setDefault('timestart', null);
//        $mform->addRule('timestart', null, null, null, 'client');

        $mform->addElement('date_time_selector', 'timestop', get_string('timestop', 'statements'),
array(
    'startyear' => 1970, 
    'stopyear'  => 2020,
    'timezone'  => 99, 
    'applydst'  => true, 
    'step'      => 1
));
        $mform->setDefault('timestop', null);
//        $mform->addRule('timestop', null, null, null, 'client');
//	}
//END OF PERSONAL CONTEST SETTINGS

        $mform->addElement('checkbox', 'virtual_olympiad', get_string('virtual_olympiad', 'statements'));
        $mform->setDefault('virtual_olympiad', 0);
     //   $mform->addRule('virtual_olympiad', null, null, null, 'client');

        $mform->addElement('text', 'virtual_duration', get_string('virtual_duration', 'statements'));
        $mform->setType('virtual_duration', PARAM_INT);
        $mform->setDefault('virtual_duration', 5*60*60);
  //      $mform->addRule('virtual_duration', null, null, null, 'client');

        $mform->addElement('textarea', 'settings', get_string('settings'));
        $mform->setType('settings', PARAM_RAW);
    //    $mform->addRule('settings', null, null, null, 'client');

        
        $this->standard_coursemodule_elements();

//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }


}
?>
