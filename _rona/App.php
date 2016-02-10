<?php

require_once Config::get('rona.core') . '/Route.php';

class App {
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public static function get($path, $components) {
		self::map('get', $path, $components);
	}

	public static function post($path, $components) {
		self::map('post', $path, $components);
	}

	public static function put($path, $components) {
		self::map('put', $path, $components);
	}

	public static function patch($path, $components) {
		self::map('patch', $path, $components);
	}

	public static function delete($path, $components) {
		self::map('delete', $path, $components);
	}

	public static function options($path, $components) {
		self::map('options', $path, $components);
	}

	public static function any($path, $components) {
		self::map(Config::get('rona.http_methods'), $path, $components);
	}
	
	public static function map($http_methods, $path, $components) {
		Route::map($http_methods, $path, $components);
	}
	
}

?>