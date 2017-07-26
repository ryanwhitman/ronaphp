<?php

namespace Rona;

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