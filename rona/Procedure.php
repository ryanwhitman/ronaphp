<?php

class Procedure {

	private static $instance;
	
	private $profilters = [];
	
	public $procedures = [];
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public static function instance() {

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}
	
	public static function profilter($name, $default_options, $function) {
		self::instance()->profilters[$name] = [
			'default_options'	=> (array) $default_options,
			'function'			=> $function
		];
	}
	
	public static function run_profilter($name, $input, $options = []) {

		// Targeted load
			$name = Rona::tLoad('profilter', $name);

		// Get the profilter
			$profilter = Helper::get(self::instance()->profilters[$name]);

		// Ensure profilter exists
			if (empty($profilter))
				throw new Exception('The profilter "' . $name . '" does not exist.');

		return $profilter['function']($input, array_merge($profilter['default_options'], $options));
	}
	
	public static function procedure($name) {
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
		
		// Create an empty array for the profiltered input
			$input_profiltered = [];
	
		// Ensure the error message array starts off empty
			$error_msgs = [];
			
		// Run the profilters
			foreach ($procedure['profilters'] as $profilter) {
				
				$ret = self::run_profilter($profilter['name'], array_merge($input_raw, $input_profiltered), $profilter['options']);
				if ($ret['success']) {
					$input_profiltered = array_merge($input_profiltered, $ret['data']);
				} else {
					$error_msgs[] = $ret['message'];
				}
			}
			
		// If there are any error messages, return the error messages
			if (!empty($error_msgs))
				return Response::false($error_msgs);
		
		// Execute the procedure
			return $procedure['execute']($input_profiltered, $input_raw);
	}

	public function get($path, $procedure) {
		return $this->map('get', $path, $procedure);
	}

	public function post($path, $procedure) {
		return $this->map('post', $path, $procedure);
	}

	public function put($path, $procedure) {
		return $this->map('put', $path, $procedure);
	}

	public function patch($path, $procedure) {
		return $this->map('patch', $path, $procedure);
	}

	public function delete($path, $procedure) {
		return $this->map('delete', $path, $procedure);
	}

	public function options($path, $procedure) {
		return $this->map('options', $path, $procedure);
	}

	public function any($path, $procedure) {
		return $this->map(Route::$http_methods, $path, $procedure);
	}

	public function map($http_methods, $path, $procedure) {
		
		Route::map($http_methods, $path, [
			'procedure'		=>	$procedure,
			'is_api'		=>	true
		]);
	}
}

?>