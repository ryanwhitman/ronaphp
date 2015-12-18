<?php

class Request {

	private static $instance;

	private
		$http_method,
		$route,
		$route_vars = [],
		$route_ids = [],
		$route_tags = [],
		$route_options = [];
	
	private function __construct() {

		// If "_http_method" override was posted, use it. Otherwise, use default
			$this->http_method = strtolower(!empty($_POST['_http_method']) ? $_POST['_http_method'] : $_SERVER['REQUEST_METHOD']);
	}

	private function __clone() {}

	private function __wakeup() {}

	private static function instance() {

		if (self::$instance == NULL) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function reset() {
		self::$instance = NULL;
	}

	public static function set($prop, $val) {

		// Ensure the $prop is valid
			if (!in_array($prop, [
				'http_method',
				'route',
				'route_vars',
				'route_ids',
				'route_tags',
				'route_options'
			])) throw new Exception($prop . ' is not a valid Request property.');

		self::instance()->{$prop} = $val;
	}

	public static function http_method() {
		return self::instance()->http_method;
	}

	public static function route() {
		return self::instance()->route;
	}

	public static function route_vars() {
		return self::instance()->route_vars;
	}

	public static function route_ids() {
		return self::instance()->route_ids;
	}

	public static function route_tags() {
		return self::instance()->route_tags;
	}

	public static function route_options() {
		return self::instance()->route_options;
	}
}

?>