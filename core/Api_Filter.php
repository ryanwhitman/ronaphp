<?php

class Api_Filter {

	private
		$http_methods,
		$type,
		$path;

	public function __construct($http_methods, $type, $path) {
		
		$this->http_methods = (array) $http_methods;
		$this->type = (string) $type;
		$this->path = (string) trim(strtolower($path), '/ ');
	}

	public function filter($name, $options = []) {

		foreach ($this->http_methods as $http_method)
			Route::instance()->routes[$http_method][$this->type][$this->path]['filters'][] = ['name' => (string) $name, 'options' => (array) $options];

		return new self($this->http_methods, $this->type, $this->path);
	}

}

?>