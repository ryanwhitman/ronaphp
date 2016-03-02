<?php

require_once Config::get('rona.core_dir') . '/Route.php';
require_once Config::get('rona.core_dir') . '/Api_Filter.php';

class Api {

	private static $instance;
	
	private $base_path = '';
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}
	
	public static function instance() {
		
		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}

	public static function set_base_path($path) {
		self::instance()->base_path = $path;
	}

	public static function get($path, $procedure) {
		return self::map('get', $path, $procedure);
	}

	public static function post($path, $procedure) {
		return self::map('post', $path, $procedure);
	}

	public static function put($path, $procedure) {
		return self::map('put', $path, $procedure);
	}

	public static function patch($path, $procedure) {
		return self::map('patch', $path, $procedure);
	}

	public static function delete($path, $procedure) {
		return self::map('delete', $path, $procedure);
	}

	public static function options($path, $procedure) {
		return self::map('options', $path, $procedure);
	}

	public static function any($path, $procedure) {
		return self::map(Config::get('rona.http_methods'), $path, $procedure);
	}

	public static function map($http_methods, $path, $procedure) {

		$path = self::instance()->base_path . $path;

		$type = Route::map($http_methods, $path, [
			'procedure'		=>	$procedure,
			'is_api'		=>	true
		]);

		return new Api_Filter($http_methods, $type, $path);
	}
}

?>