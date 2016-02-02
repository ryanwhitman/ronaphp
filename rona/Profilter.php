<?php

class Profilter {

	private static $instance;
	
	private $profilters = [];
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public static function instance() {

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}
	
	public static function define($name, $default_options, $function) {
		self::instance()->profilters[$name] = [
			'default_options'	=> (array) $default_options,
			'function'			=> $function
		];
	}
	
	public static function run($name, $input, $options = []) {

		// Targeted load
			$name = Rona::tLoad('profilter', $name);

		// Get the profilter
			$profilter = Helper::get(self::instance()->profilters[$name]);

		// Ensure profilter exists
			if (empty($profilter))
				throw new Exception('The profilter "' . $name . '" does not exist.');

		return $profilter['function']($input, array_merge($profilter['default_options'], $options));
	}
}

?>