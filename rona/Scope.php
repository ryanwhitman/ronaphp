<?php

class Scope {

	private static $instance;
	
	private function __construct() {}

	private function __clone() {}

	private function __wakeup() {}
	
	private static function instance() {

		if (self::$instance == NULL) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function _() {

		switch (func_num_args()) {

			case 0:
				return self::instance();
			break;

			case 1:
				return isset(get_object_vars(self::instance())[func_get_args()[0]]) ? get_object_vars(self::instance())[func_get_args()[0]] : NULL;
			break;

			case 2:
				return self::instance()->{func_get_args()[0]} = func_get_args()[1];
			break;

			default:
				throw new Exception('The scope method requires 0, 1, or 2 arguments.');
		}
	}
}

?>