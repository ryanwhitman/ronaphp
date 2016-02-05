<?php

require_once __DIR__ . '/Procedure_.php';
require_once __DIR__ . '/Filter.php';

class Procedure {

	private static $instance;
	
	public $procedures = [];
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public static function instance() {

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}
	
	public static function define($name) {
		return new Procedure_($name);
	}
	
	public static function run($name, $input_raw = []) {

		// Targeted load
			$name = Rona::tLoad('procedure', $name);

		// Get the procedure
			$procedure = Helper::get(self::instance()->procedures[$name]);

		// Ensure procedure exists
			if (empty($procedure))
				throw new Exception('The procedure "' . $name . '" does not exist.');
		
		// Create an empty array for the filtered input
			$input_filtered = [];
	
		// Ensure the error message array starts off empty
			$error_msgs = [];
			
		// Run the filters
			foreach ($procedure['filters'] as $filter) {
				
				$ret = Filter::run($filter['name'], array_merge($input_raw, $input_filtered), $filter['options']);
				if ($ret['success'])
					$input_filtered = array_merge($input_filtered, $ret['data']);
				else
					$error_msgs[] = $ret['message'];
			}
			
		// If there are any error messages, return the error messages
			if (!empty($error_msgs))
				return Response::false($error_msgs);
		
		// Execute the procedure
			return $procedure['execute']($input_filtered, $input_raw);
	}
}

?>