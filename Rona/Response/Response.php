<?php
/**
 * RonaPHP Response is a standardized response class.
 *
 * @package RonaPHP Response
 * @copyright Copyright (c) 2017 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT MIT
 * @version 1.0.0
 * @link https://github.com/RyanWhitman/ronaphp_response
 */

namespace Rona\Response;

class Response {

	public $success;

	public $messages;

	public $message;

	public $data;
	
	public function __construct($success = NULL, $messages = [], $data = NULL) {
		$this->set($success, $messages, $data);
	}

	public function set($success, $messages = [], $data = NULL) {
		$messages = (array) $messages;
		$this->success = (bool) $success;
		$this->messages = $messages;
		$this->message = $messages[0];
		$this->data = $data;

		return $this;
	}

	public function set_success($messages = [], $data = NULL) {
		$this->set(true, $messages, $data);

		return $this;
	}

	public function set_failure($messages = [], $data = NULL) {
		$this->set(false, $messages, $data);

		return $this;
	}
}