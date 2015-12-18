<?php

class Procedure_ {

	private
		$name,
		$preps = [],
		$route_obj;
	
	function __construct($name) {
		$this->name = (string) $name;

		return $this;
	}
	
	public function route() {
		$this->route_obj = new Procedure_Route($this);
		return $this->route_obj;
	}
	
	public function prep($name, $options = []) {
		$this->preps[] = [
			'name'		=>	(string) $name,
			'options'	=>	(array) $options
		];

		return $this;
	}
	
	public function execute($function) {
		Procedure::instance()->procedures[$this->name] = [
			'preps'		=>	$this->preps,
			'execute'	=>	$function
		];
		
		if (!empty($this->route_obj->http_methods) && !empty($this->route_obj->path)) {
			Route::custom($this->route_obj->http_methods, '', $this->route_obj->path, [
				'procedure'		=>	$this->name
			]);
		}
	}
}

?>