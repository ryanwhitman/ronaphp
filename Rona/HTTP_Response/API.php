<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.4.0
 */

namespace Rona\HTTP_Response;

class API {

	public $messages;

	public $data;

	public function set($messages = [], $data = NULL) {
		$this->set_messages($messages);
		$this->set_data($data);
	}

	public function set_messages($messages = []): self {
		$this->messages = (array) $messages;
		return $this;
	}

	public function set_data($data = NULL): self {
		$this->data = $data;
		return $this;
	}
}