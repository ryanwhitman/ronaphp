<?php

class Controller {

	private static $instance;
	
	private
		$controllers = [],
		$controllers_run = [];
	
	private function __construct() {}

	private function __clone() {}

	private function __wakeup() {}

	private static function instance() {

		if (self::$instance == NULL) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public static function controller($name, $function) {
		self::instance()->controllers[$name] = $function;
	}
	
	public static function run($full_name, $args = NULL) {

		// Load the group
			$name = App::load_group('controllers.' . $full_name);

		// Get the controller
			$controller = Helper::get(self::instance()->controllers[$name]);

		// Ensure controller exists
			if (empty($controller))
				throw new Exception('The controller "' . $name . '" does not exist.');

		$args = func_get_args();
		unset($args[0]);
		call_user_func_array($controller, $args);
		self::instance()->controllers_run[] = $name;
	}
	
	public static function was_run($controller) {
		return in_array($controller, self::instance()->controllers_run);
	}
}

?>