<?php
/**
 * RonaPHP ParamExam is a tool used to validate, sanitize, and alter parameters.
 *
 * @package RonaPHP ParamExam
 * @copyright Copyright (c) 2017 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT MIT
 * @version .5.1.1
 * @link https://github.com/RyanWhitman/ronaphp_paramexam
 */

namespace Rona\ParamExam;
use Rona\Helper\Helper as Helper;

/**
 * The class used to create a set of parameters.
 */
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

		return $this->param($param, true, Helper::array_get($props, 'help_text', ''), Helper::array_get($props, 'filters', []), Helper::array_get($props, 'options', []));
	}
	
	public function opt_param($param, $props = []) {

		return $this->param($param, false, Helper::array_get($props, 'help_text', ''), Helper::array_get($props, 'filters', []), Helper::array_get($props, 'options', []));
	}
}