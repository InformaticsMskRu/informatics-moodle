<?php

require_once($CFG->libdir . "/externallib.php");

class local_pynformatics_external extends external_api {
	public static function has_capability_parameters() {
		return new external_function_parameters(
			array(
				'moodlesid' => new external_value(PARAM_TEXT, 'MoodleSid from cookies', VALUE_DEFAULT, ''),
				'capability' => new external_value(PARAM_TEXT, 'String capability', VALUE_DEFAULT, ''),
			)
		);
	}

	public static function has_capability($moodlesid = '', $capability = '') {
		global $USER;
		
		$params = self::validate_parameters(self::has_capability_parameters(),
			array(
				'moodlesid' => $moodlesid,
				'capability' => $capability
			)
		);

		$context = context_system::instance();
		self::validate_context($context);

		$session_info = \core\session\manager::time_remaining($params['moodlesid']);
		$userid = $session_info['userid'];
		if (!has_capability('local/pynformatics:check_capability', $context)) {

			$flag = $params['capability'] && has_capability($params['capability'], $context, $userid);
			return array('user_id' => $userid, 'capability'=> array('name'=>$params['capability'],'status'=>$flag));
		} else {
			return $userid."_".' fail';
		}
	}

	public static function has_capability_returns() {
		return new external_single_structure(array(
			'user_id'=> new external_value(PARAM_INT, 'user id'),
			'capability' => new external_single_structure(array(
				'name' => new external_value(PARAM_TEXT, 'capability name'),
				'status' =>  new external_value(PARAM_BOOL, 'value')
			))
		));
	}
}

?>

