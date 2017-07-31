<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2017 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
 */

namespace Rona\ParamExam;
use Rona\Helper;

class ParamSet {

	public $params = [];
	
	public function param($param, $is_reqd, $help_text, $filters = [], $options = []) {

		// Prevent empty strings from resulting in [''] - an indexed array with an empty string as a value
		if (empty($filters))
			$filters = [];

		$this->params[(string) $param] = [
			'is_reqd'		=> (bool) $is_reqd,
			'help_text'		=> (string) $help_text,
			'filters'		=> (array) $filters,
			'options'		=> (array) $options
		];

		return $this;
	}
	
	public function reqd_param($param, $props = []) {

		return $this->param($param, true, $props['help_text'] ?? '', $props['filters'] ?? [], $props['options'] ?? []);
	}
	
	public function opt_param($param, $props = []) {

		return $this->param($param, false, $props['help_text'] ?? '', $props['filters'] ?? [], $props['options'] ?? []);
	}
}