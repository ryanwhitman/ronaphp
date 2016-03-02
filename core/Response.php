<?php

require_once Config::get('rona.core_dir') . '/Response_.php';

class Response {
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public static function set($success, $messages = [], $data = []) {
		return new Response_($success, $messages, $data);
	}

}