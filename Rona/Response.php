<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2017 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
 */

namespace Rona;

class Response {

	public $success;

	public $messages;

	public $data;
	
	public function __construct(bool $success = NULL, $messages = [], $data = NULL) {
		$this->set($success, $messages, $data);
	}

	public function set(bool $success = NULL, $messages = [], $data = NULL): self {
		$this->success = $success;
		$this->messages = (array) $messages;
		$this->data = $data;

		return $this;
	}

	public function set_success($messages = [], $data = NULL): self {
		$this->set(true, $messages, $data);

		return $this;
	}

	public function set_failure($messages = [], $data = NULL): self {
		$this->set(false, $messages, $data);

		return $this;
	}
}