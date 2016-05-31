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
		$input_processed = $error_msgs = [];

		// Run through each param
		foreach ($procedure['params'] as $param => $props) {

			// Establish the initial value
			$val = Helper::array_get($input_raw, $param);

			// Find the default value, if applicable
			if (Helper::is_nullOrEmptyString($val) && !Helper::is_nullOrEmptyString(Helper::array_get($props, 'options.default')))
				$val = $props['options']['default'];

			// Instantiate the $dependencies_met boolean as true
			$dependencies_met = true;

			// If dependencies were defined, then run filters only if those conditions are met
			foreach (Helper::array_get($props, 'options.dependencies', []) as $dependent_param => $dependent_val) {

				if (!Helper::array_get($input_processed, $dependent_param) === $dependent_val) {
					$dependencies_met = false;
					break;
				}
			}

			// If dependent_param was declared, then run filters only if that param exists and is not null
			$dependent_param = Helper::array_get($props, 'options.dependent_param');
			if (isset($dependent_param) && !isset($input_processed[$dependent_param]))
				$dependencies_met = false;

			// If dependent_true was declared, then run filters only if that param exists, is not null, and evaluates to true
			$dependent_true = Helper::array_get($props, 'options.dependent_true');
			if (isset($dependent_true) && (!isset($input_processed[$dependent_true]) || !$input_processed[$dependent_true]))
				$dependencies_met = false;

			// If dependent_false was declared, then run filters only if that param exists, is not null, and evaluates to false
			$dependent_false = Helper::array_get($props, 'options.dependent_false');
			if (isset($dependent_false) && (!isset($input_processed[$dependent_false]) || $input_processed[$dependent_false]))
				$dependencies_met = false;

			// If the dependency checks were met, then proceed
			if ($dependencies_met) {

				// If the param is required and the value is either null or an empty string, record the error and move to the next param
				if ($props['is_reqd'] && Helper::is_nullOrEmptyString($val)) {
					$error_msgs[] = 'You must provide ' . Helper::indefinite_article($props['label']) . '.';
					continue;
				}

				// If the value is just an empty string, just trim it and leave it be
				if (Helper::is_emptyString($val))
					$val = trim($val);

				// If the value is not null, then it must have a value of some sort, so run it through the filters
				else if (!is_null($val)) {

					foreach ($props['filters'] as $k => $v) {

						if (is_array($v)) {
							$name = $k;
							$options = $v;
						} else {
							$name = $v;
							$options = [];
						}

						$res = Filter::run($name, $val, $props['label'], $options);
						if ($res->success)
							$val = $res->data;

						// Since this value created an error in the filter, we'll record the error message and skip any additional filters for this param
						else {
							$error_msgs = array_merge($error_msgs, $res->messages);
							continue 2;
						}
					}
				}
			}

			// Since the dependency checks failed, we'll just insert a NULL value for the param
			else
				$val = NULL;

			// Save the value into the $input_processed array
			$to_param = Helper::array_get($props['options'], 'to_param');
			$param = !Helper::is_nullOrEmptyString($to_param) ? $to_param : $param;

			$path = Helper::array_get($props['options'], 'to_array') . '.' . $param;
			$path = trim($path, '. ');

			Helper::array_set($input_processed, $path, $val);
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