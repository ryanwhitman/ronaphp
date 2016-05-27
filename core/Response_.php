<?php

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