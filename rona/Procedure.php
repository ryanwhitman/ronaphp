<?php

class Procedure {

	private static $instance;
	
	private $preps = [];
	
	public $procedures = [];
	
	private function __construct() {}

	private function __clone() {}

	private function __wakeup() {}

	public static function instance() {

		if (self::$instance == NULL) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public static function prep($name, $default_options, $function) {
		self::instance()->preps[$name] = [
			'default_options'	=> (array) $default_options,
			'function'			=> $function
		];
	}
	
	public static function run_prep($full_name, $input, $options = []) {

		// Load the group
			$name = App::load_group('preps.' . $full_name);

		// Get the prep
			$prep = Helper::get(self::instance()->preps[$name]);

		// Ensure prep exists
			if (empty($prep))
				throw new Exception('The prep "' . $name . '" does not exist.');

		return $prep['function']($input, array_merge($prep['default_options'], $options));
	}
	
	public static function procedure($name) {
		return new Procedure_($name);
	}
	
	public static function run($full_name, $input_raw = []) {

		// Load the group
			$name = App::load_group('procedures.' . $full_name);

		// Get the procedure
			$procedure = Helper::get(self::instance()->procedures[$name]);

		// Ensure procedure exists
			if (empty($procedure))
				throw new Exception('The procedure "' . $name . '" does not exist.');
		
		// Create an empty array for the prepped input
			$input_prepped = [];
	
		// Ensure the error message array starts off empty
			$error_msgs = [];
			
		// Run the preps
			foreach ($procedure['preps'] as $prep) {
				
				$ret = self::run_prep($prep['name'], array_merge($input_raw, $input_prepped), $prep['options']);
				if ($ret['success']) {
					$input_prepped = array_merge($input_prepped, $ret['data']);
				} else {
					$error_msgs[] = $ret['message'];
				}
			}
			
		// If there are any error messages, return the error messages
			if (!empty($error_msgs)) {
				return App::ret(false, $error_msgs);
			}
		
		// Execute the procedure
			return $procedure['execute']($input_prepped, $input_raw);
	}
}

?>