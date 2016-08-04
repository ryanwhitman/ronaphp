<?php
/**
 * This file houses the Response_ class.
 *
 * @package RonaPHP
 * @copyright Copyright (c) 2016 Ryan Whitman (http://www.ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT MIT
 * @version .5.4.1
 * @link https://github.com/RyanWhitman/ronaphp
 * @since .5.4.1
 */

class Response_ {

	public
		$success,
		$messages,
		$data;
	
	public function __construct($success, $messages, $data) {
		$this->success = (bool) $success;
		$this->messages = (array) $messages;
		$this->data = $data;
	}

}