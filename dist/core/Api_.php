<?php
/**
 * This file houses the Api_ class.
 *
 * @package RonaPHP
 * @copyright Copyright (c) 2016 Ryan Whitman (http://www.ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT MIT
 * @version .5.4.1
 * @link https://github.com/RyanWhitman/ronaphp
 * @since .5.4.1
 */

class Api_ {

	private
		$http_methods,
		$type,
		$path;

	public function __construct($http_methods, $type, $path) {
		
		$this->http_methods = (array) $http_methods;
		$this->type = (string) $type;
		//$this->path = (string) trim(strtolower($path), '/');
		$this->path = (string) strtolower($path);
	}

	private function set_component($component, $key, $val) {

		foreach ($this->http_methods as $http_method)
			Route::instance()->routes[$http_method][$this->type][$this->path][$component][$key] = $val;

		return new self($this->http_methods, $this->type, $this->path);
	}

	public function authenticate($set_auth_user_id_as = NULL) {

		foreach ($this->http_methods as $http_method) {
			Route::instance()->routes[$http_method][$this->type][$this->path]['authenticate'] = true;
			Route::instance()->routes[$http_method][$this->type][$this->path]['set_auth_user_id_as'] = $set_auth_user_id_as;
		}

		return new self($this->http_methods, $this->type, $this->path);
	}

	public function authorize($procedure, $switches = []) {

		return $this->set_component('authorizations', $procedure, $switches);
	}

	public function set_param($param, $val) {

		return $this->set_component('set_param', (string) $param, $val);
	}

	public function onAuthentication_failure($func) {

		return $this->set_component('hooks', 'onAuthentication_failure', $func);
	}

	public function onParam_failure($func) {

		return $this->set_component('hooks', 'onParam_failure', $func);
	}

	public function onAuthorization_failure($func) {

		return $this->set_component('hooks', 'onAuthorization_failure', $func);
	}

	public function onSuccess($func) {

		return $this->set_component('hooks', 'onSuccess', $func);
	}
}