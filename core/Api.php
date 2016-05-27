<?php

require_once Config::get('rona.core_dir') . '/Route.php';
require_once Config::get('rona.core_dir') . '/Api_.php';

class Api {

	private static $instance;

	private $no_route = '';

	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public static function instance() {

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}

	public static function get($path, $procedure, $authenticate, $set_auth_user_id_as = NULL) {
		return self::map('get', $path, $procedure, $authenticate, $set_auth_user_id_as);
	}

	public static function post($path, $procedure, $authenticate, $set_auth_user_id_as = NULL) {
		return self::map('post', $path, $procedure, $authenticate, $set_auth_user_id_as);
	}

	public static function put($path, $procedure, $authenticate, $set_auth_user_id_as = NULL) {
		return self::map('put', $path, $procedure, $authenticate, $set_auth_user_id_as);
	}

	public static function patch($path, $procedure, $authenticate, $set_auth_user_id_as = NULL) {
		return self::map('patch', $path, $procedure, $authenticate, $set_auth_user_id_as);
	}

	public static function delete($path, $procedure, $authenticate, $set_auth_user_id_as = NULL) {
		return self::map('delete', $path, $procedure, $authenticate, $set_auth_user_id_as);
	}

	public static function options($path, $procedure, $authenticate, $set_auth_user_id_as = NULL) {
		return self::map('options', $path, $procedure, $authenticate, $set_auth_user_id_as);
	}

	public static function any($path, $procedure, $authenticate, $set_auth_user_id_as = NULL) {
		return self::map(Config::get('rona.http_methods'), $path, $procedure, $authenticate, $set_auth_user_id_as);
	}

	public static function map($http_methods, $path, $procedure, $authenticate, $set_auth_user_id_as = NULL) {

		$type = Route::map($http_methods, $path, [
			'is_api'				=> true,
			'procedure'				=> $procedure,
			'authenticate'			=> $authenticate,
			'set_auth_user_id_as'	=> $set_auth_user_id_as
		]);

		return new Api_($http_methods, $type, $path);
	}
	
	public static function no_route($messages, $data = []) {
		self::instance()->no_route = Response::set(false, $messages, $data);
	}

	public static function get_no_route() {
		return self::instance()->no_route;
	}
}