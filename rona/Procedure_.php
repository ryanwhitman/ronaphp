<?php

class Procedure_ {

	private
		$name,
		$profilters = [];
	
	function __construct($name) {
		$this->name = (string) $name;

		return $this;
	}
	
	public function profilter($name, $options = []) {
		$this->profilters[] = [
			'name'		=>	(string) $name,
			'options'	=>	(array) $options
		];

		return $this;
	}
	
	public function execute($function) {
		Procedure::instance()->procedures[$this->name] = [
			'profilters'	=>	$this->profilters,
			'execute'		=>	$function
		];
	}
}

?>