<?php

class Procedure_ {

	private
		$name,
		$filters = [];
	
	function __construct($name) {
		$this->name = (string) $name;

		return $this;
	}
	
	public function filter($name, $options = []) {
		$this->filters[] = [
			'name'		=>	(string) $name,
			'options'	=>	(array) $options
		];

		return $this;
	}
	
	public function execute($function) {
		Procedure::instance()->procedures[$this->name] = [
			'filters'	=>	$this->filters,
			'execute'		=>	$function
		];
	}
}

?>