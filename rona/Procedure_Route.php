<?php

class Procedure_Route {

	public
		$procedure_obj,
		$http_methods,
		$path;
	
	public function __construct($procedure_obj) {

		// This class should only be instantiated from within the Procedure_ class
			if (!is_object($procedure_obj) || !is_a($procedure_obj, 'Procedure_'))
				throw new Exception('The Procedure_Route class should only be instantiated from within the Procedure_ class.');

		$this->procedure_obj = $procedure_obj;
		return $this;
	}

	public function get($path) {
		return $this->custom('get', $path);
	}

	public function post($path) {
		return $this->custom('post', $path);
	}

	public function put($path) {
		return $this->custom('put', $path);
	}

	public function patch($path) {
		return $this->custom('patch', $path);
	}

	public function delete($path) {
		return $this->custom('delete', $path);
	}

	public function head($path) {
		return $this->custom('head', $path);
	}

	public function options($path) {
		return $this->custom('options', $path);
	}

	public function any($path) {
		return $this->custom(Route::$http_methods, $path);
	}

	public function custom($http_methods, $path) {
		$this->http_methods = $http_methods;
		$this->path = $path;
		return $this->procedure_obj;
	}

}

?>