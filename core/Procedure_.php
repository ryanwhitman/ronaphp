<?php

class Procedure_ {

	private
		$name,
		$params = [];
	
	function __construct($name) {
		$this->name = (string) $name;

		return $this;
	}
	
	public function param($param, $is_reqd, $filters = [], $options = []) {

		// Prevent empty strings from resulting in [''] - an indexed array with an empty string as a value
		if (empty($filters))
			$filters = [];

		$this->params[(string) $param] = [
			'is_reqd'	=>	(bool) $is_reqd,
			'filters'	=>	(array) $filters,
			'options'	=>	(array) $options
		];

		return $this;
	}

	public function execute($function) {
		Procedure::instance()->procedures[$this->name] = [
			'params'	=>	$this->params,
			'execute'	=>	$function
		];
	}
}