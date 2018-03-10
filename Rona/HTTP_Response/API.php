<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
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