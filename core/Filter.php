<?php

class Filter {

	private static $instance;
	
	private $filters = [];
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public static function instance() {

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}
	
	public static function set($name, $default_options, $function) {
		self::instance()->filters[$name] = [
			'default_options'	=> (array) $default_options,
			'function'			=> $function
		];
	}
	
	public static function run($name, $input, $options = []) {

		// Targeted load
			$name = Rona::tLoad('filter', $name);

		// Get the filter
			$filter = Helper::array_get(self::instance()->filters, $name);

		// Ensure filter exists
			if (empty($filter))
				throw new Exception('The filter "' . $name . '" does not exist.');

		return $filter['function']($input, array_merge($filter['default_options'], $options));
	}
}

?>