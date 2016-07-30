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

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}
	
	public static function set($name, $function) {
		self::instance()->controllers[Rona::get_tLoad_namespace() . '.' . $name] = $function;
	}
	
	public static function run($fullname, $args = NULL) {

		// Targeted load
		Rona::tLoad('controller', $fullname);

		// Get the controller
		$controller = Helper::array_get(self::instance()->controllers, [$fullname]);

		// Ensure controller exists
		if (empty($controller))
			throw new Exception('The controller "' . $fullname . '" does not exist.');

		$args = func_get_args();
		unset($args[0]);
		call_user_func_array($controller, $args);
		self::instance()->controllers_run[] = $fullname;
	}
	
	public static function was_run($controller) {
		return in_array($controller, self::instance()->controllers_run);
	}
}