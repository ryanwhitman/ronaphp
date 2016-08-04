<?php
/**
 * This file houses the Response class. Additionally, it loads the Response class dependencies.
 *
 * @package RonaPHP
 * @copyright Copyright (c) 2016 Ryan Whitman (http://www.ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT MIT
 * @version .5.4.1
 * @link https://github.com/RyanWhitman/ronaphp
 * @since .5.4.1
 */

require_once __DIR__ . '/Response_.php';

class Response {
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public static function set($success, $messages = [], $data = []) {
		return new Response_($success, $messages, $data);
	}

}