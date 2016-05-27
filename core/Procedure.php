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

	private static function get($name) {

		// Targeted load
		$name = Rona::tLoad('procedure', $name);

		// Get the procedure
		$procedure = Helper::array_get(self::instance()->procedures, $name);

		// Ensure procedure exists
		if (empty($procedure))
			throw new Exception('The procedure "' . $name . '" does not exist.');

		return $procedure;
	}

	public static function process_input($name, $input_raw = []) {

		// Get the procedure
		$procedure = self::get($name);

		// Establish arrays
		$input_processed = $error_msgs = $missing_params = [];

		// Run through each param
		foreach ($procedure['params'] as $param => $props) {

			// Establish the initial value
			$val = Helper::array_get($input_raw, $param);

			// Find the default value, if applicable
			if (is_null($val) && isset($props['options']['default']))
				$val = $props['options']['default'];

			// If dependent_param was declared, then proceed only if that param exists and is not null
			$dependent_param = Helper::array_get($props, 'options.dependent_param');
			if (isset($dependent_param) && !isset($input_processed[$dependent_param]))
				continue;

			// If dependent_true was declared, then proceed only if that param exists, is not null, and evaluates to true
			$dependent_true = Helper::array_get($props, 'options.dependent_true');
			if (isset($dependent_true) && (!isset($input_processed[$dependent_true]) || !$input_processed[$dependent_true]))
				continue;

			// If dependent_false was declared, then proceed only if that param exists, is not null, and evaluates to false
			$dependent_false = Helper::array_get($props, 'options.dependent_false');
			if (isset($dependent_false) && (!isset($input_processed[$dependent_false]) || $input_processed[$dependent_false]))
				continue;

			// If the param was not passed in at all, then deal with it as either is_reqd or !is_reqd
			if (is_null($val)) {

				if ($props['is_reqd'])
					$missing_params[] = $param;

				continue;
			}

			// Run the filters
			foreach ($props['filters'] as $k => $v) {

				if (is_array($v)) {
					$name = $k;
					$options = $v;
				} else {
					$name = $v;
					$options = [];
				}

				$res = Filter::run($name, $val, $options);
				if ($res->success)
					$val = $res->data;
				else {
					$error_msgs = array_merge($error_msgs, $res->messages);
					continue 2;
				}
			}

			// Save the value into the $input_processed array
			$to_param = Helper::array_get($props['options'], 'to_param');
			$param = !Helper::is_nullOrEmptyString($to_param) ? $to_param : $param;

			$path = Helper::array_get($props['options'], 'to_array') . '.' . $param;
			$path = trim($path, '. ');

			Helper::array_set($input_processed, $path, $val);
		}

		// Add an error message for the missing params
		$num_missing_params = count($missing_params);
		if ($num_missing_params) {
			$mp_msg = 'Missing param' . ($num_missing_params == 1 ? '' : 's') . ': ' . (Helper::array_to_csv($missing_params));
			array_unshift($error_msgs, $mp_msg);
		}
			
		// If there are any error messages, return them
		if (!empty($error_msgs))
			return Response::set(false, $error_msgs);

		// Otherwise, return processed input
		return Response::set(true, '', $input_processed);
	}

	public static function execute($name, $input_processed = []) {		

		// Get the procedure
		$procedure = self::get($name);
		
		// Execute the procedure and return the response object
		return $procedure['execute']($input_processed);
	}
	
	public static function run($name, $input_raw = []) {

		// Process the input
		$res = self::process_input($name, $input_raw);
		if (!$res->success)
			return $res;

		$input_processed = $res->data;
		
		// Execute the procedure and return the response object
		return self::execute($name, $input_processed);
	}
}