<?php

require_once Config::get('rona.core_dir') . '/Procedure_.php';
require_once Config::get('rona.core_dir') . '/Filter.php';

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
	
	public static function set($name) {
		return new Procedure_($name);
	}
	
	public static function run($name, $input_unfiltered = []) {

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
				
				$res = Filter::run($filter['name'], array_merge($input_unfiltered, $input_filtered), $filter['options']);
				if ($res->success)
					$input_filtered = array_merge($input_filtered, $res->data);
				else
					$error_msgs[] = $res->messages;
			}
			
		// If there are any error messages, return the error messages
			if (!empty($error_msgs))
				return Response::set(false, $error_msgs);
		
		// Execute the procedure
			return $procedure['execute']($input_filtered);
	}
}

?>