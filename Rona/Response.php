<?php

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