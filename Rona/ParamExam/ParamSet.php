<?php

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

		return $this->param($param, true, Helper::array_get($props, 'help_text', ''), Helper::array_get($props, 'filters', []), Helper::array_get($props, 'options', []));
	}
	
	public function opt_param($param, $props = []) {

		return $this->param($param, false, Helper::array_get($props, 'help_text', ''), Helper::array_get($props, 'filters', []), Helper::array_get($props, 'options', []));
	}
}