<?php
/**
 * This file houses the Procedure_ class.
 *
 * @package RonaPHP
 * @copyright Copyright (c) 2016 Ryan Whitman (http://www.ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT MIT
 * @version .5.4.1
 * @link https://github.com/RyanWhitman/ronaphp
 * @since .5.4.1
 */

class Procedure_ {

	private
		$name,
		$params = [];
	
	function __construct($name) {
		$this->name = (string) $name;

		return $this;
	}
	
	public function param($param, $label, $is_reqd, $filters = [], $options = []) {

		// Prevent empty strings from resulting in [''] - an indexed array with an empty string as a value
		if (empty($filters))
			$filters = [];

		$this->params[(string) $param] = [
			'label'		=>	(string) $label,
			'is_reqd'	=>	(bool) $is_reqd,
			'filters'	=>	(array) $filters,
			'options'	=>	(array) $options
		];

		return $this;
	}
	
	public function reqd_param($param, $label, $filters = [], $options = []) {

		return $this->param($param, $label, true, $filters, $options);
	}
	
	public function opt_param($param, $label, $filters = [], $options = []) {

		return $this->param($param, $label, false, $filters, $options);
	}

	public function execute($function) {
		Procedure::instance()->procedures[$this->name] = [
			'params'	=>	$this->params,
			'execute'	=>	$function
		];
	}
}