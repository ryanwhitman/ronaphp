<?php

class Response {
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public function true($messages = [], $data = []) {
		return self::custom(true, $messages, $data);
	}

	public function false($messages = [], $data = []) {
		return self::custom(false, $messages, $data);
	}
	
	public static function custom($success, $messages = [], $data = []) {
		return [
			'success'	=>	(bool) $success,
			'messages'	=>	(array) $messages,
			'data'		=>	(array) $data
		];
	}

}