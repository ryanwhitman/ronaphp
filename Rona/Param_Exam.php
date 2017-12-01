<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2017 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
 */

namespace Rona;

class Param_Exam {

	protected $module;

	protected $params = [];

	public function __construct(\Rona\Module $module) {

		// Set the module.
		$this->module = $module;
	}
	
	public function param(string $param, bool $is_reqd, array $filters = [], array $options = []) {

		// Store the param and its properties.
		$this->params[$param] = [
			'is_reqd'		=> $is_reqd,
			'filters'		=> $filters,
			'options'		=> $options
		];
	}
	
	public function reqd_param(string $param, array $filters = [], array $options = []) {

		// Run the param method.
		return $this->param($param, true, $filters, $options);
	}
	
	public function opt_param(string $param, array $filters = [], array $options = []) {

		// Run the param method.
		return $this->param($param, false, $filters, $options);
	}

	public function examine(array $raw_data = []): Param_Exam_Response {

		// Establish arrays
		$processed_data = $fail_data = [];

		// Run through each param
		foreach ($this->params as $param => $props) {

			// Establish the initial value
			$val = Helper::array_get($raw_data, $param);

			// Find the default value, if applicable
			if (Helper::is_nullOrEmptyString($val) && !Helper::is_nullOrEmptyString(Helper::array_get($props, 'options.default')))
				$val = $props['options']['default'];

			// If dependencies were defined, then run filters only if those conditions are met
			foreach ($props['options']['dependencies'] ?? [] as $dependent_param => $dependent_val) {

				if (!Helper::array_get($processed_data, $dependent_param) === $dependent_val)
					continue 2;
			}

			// If dependent_param was declared, then proceed only if that param exists and is not null
			$dependent_param = Helper::array_get($props, 'options.dependent_param');
			if (isset($dependent_param) && !isset($processed_data[$dependent_param]))
				continue;

			// If dependent_true was declared, then proceed only if that param exists, is not null, and evaluates to true
			$dependent_true = Helper::array_get($props, 'options.dependent_true');
			if (isset($dependent_true) && (!isset($processed_data[$dependent_true]) || !$processed_data[$dependent_true]))
				continue;

			// If dependent_false was declared, then proceed only if that param exists, is not null, and evaluates to false
			$dependent_false = Helper::array_get($props, 'options.dependent_false');
			if (isset($dependent_false) && (!isset($processed_data[$dependent_false]) || $processed_data[$dependent_false]))
				continue;

			// If the param is required and the value is either null or an empty string, record the fail data and move to the next param.
			if ($props['is_reqd'] && Helper::is_nullOrEmptyString($val)) {
				$fail_data[$param] = [
					'val'			=> $val,
					'tag'			=> 'non_existent',
					'is_reqd'		=> $props['is_reqd'],
					'filters'		=> $props['filters'],
					'options'		=> $props['options']
				];
				continue;
			}

			// If the param is null, skip it since it passed the 'required check' above.
			if (is_null($val))
				continue;

			// If the param is just an empty string and the "allow empty string" option was set, just trim it and leave it be
			if (Helper::is_emptyString($val) && Helper::array_get($props, 'options.allow_empty_string'))
				$val = trim($val);

			// The param has a value of some sort and empty strings are disallowed, so run it through the filters
			else {

				/*
				Filters are set as indexed arrays and each value can be one of the following:
				- [filter_group1.filter_name1, function() {}]
				- [filter_group1.filter_name1, filter_group2.filter_name2]
				- [filter_group1.filter_name1, [filter_group2.filter_name2, [option1 => option1_setting]]]
				- [filter_group1.filter_name1, [[module1, filter_group2.filter_name2], [option1 => option1_setting]]]
				 */

				foreach ($props['filters'] as $filter) {

					// Create an empty array to hold the filter that is found.
					$f = [];

					// By default, there are not custom options.
					$custom_options = [];

					// When the filter is just an anonymous function:
					if ($filter instanceof \Closure) {
						$f['default_options'] = [];
						$f['callback'] = $filter;
					}

					// When the filter is a string:
					else if (is_string($filter)) {
						$found_filter = $this->module->get_param_filter($filter);
						if ($found_filter)
							$f = $found_filter;
					}

					// When the filter is an array with either 1 or 2 parts:
					else if (
						is_array($filter) &&
						(
							(
								count($filter) == 1 &&
								isset($filter[0])
							) ||
							(
								count($filter) == 2 &&
								isset($filter[0]) &&
								isset($filter[1])
							)
						)
					) {

						// Default the found filter to false.
						$found_filter = false;

						// When index 0 of the array is just a string:
						if (is_string($filter[0]))
							$found_filter = $this->module->get_param_filter($filter[0]);

						// When index 0 of the array is itself an array with 2 parts:
						else if (is_array($filter[0]) && count($filter[0]) == 2 && isset($filter[0][0]) && isset($filter[0][1])) {

							// The filter module should be stored in index 0.
							$filter_module = $filter[0][0];

							// The filter name should be stored in index 1.
							$filter_name = $filter[0][1];

							// When the filter module is just a string, convert it to a module instance.
							if (is_string($filter_module))
								$filter_module = $this->module->get_module($filter_module);

							// If the filter module is a module instance, grab the param filter.
							if ($filter_module instanceof Module)
								$found_filter = $filter_module->get_param_filter($filter_name);
						}

						// If a callback was found, store the filter.
						if ($found_filter) {
							$f = $found_filter;
							$custom_options = count($filter) == 1 ? [] : $filter[1];
						}
					}

					// Ensure a valid filter was found.
					if (empty($f))
						throw new \Exception('The param filter ' . json_encode($filter) . ' identified in the module "' . $this->module->get_id() . '" is not valid.');

					// Merge the option arrays
					$filter_options = array_merge($f['default_options'], $custom_options);

					// Run the filter
					$res = $f['callback']($val, $filter_options);

					// Ensure the response is the correct type.
					if (!is_a($res, '\Rona\Param_Filter_Response'))
						throw new \Exception("The param filter did not return an instance of \Rona\Param_Filter_Response.");

					// If the response was successful, $val should be set to the response data.
					if ($res->success)
						$val = $res->data;

					// Since this value created a failure in the filter, record the fail data and skip any additional filters for this param.
					else {
						$fail_data[$param] = [
							'val'				=> $val,
							'tag'				=> $res->tag,
							'is_reqd'			=> $props['is_reqd'],
							'filters'			=> $props['filters'],
							'options'			=> $props['options'],
							'filter_options'	=> $filter_options,
							'filter_res_data'	=> $res->data
						];
						continue 2;
					}
				}
			}

			// Save the value into the $processed_data array
			$to_param = Helper::array_get($props['options'], 'to_param');
			$param = !Helper::is_nullOrEmptyString($to_param) ? $to_param : $param;

			$path = Helper::array_get($props['options'], 'to_array') . '.' . $param;
			$path = trim($path, '. ');

			Helper::array_set($processed_data, $path, $val);
		}
		
		// If there are fail data, return them.
		if (!empty($fail_data))
			return new Param_Exam_Response(false, $fail_data);

		// Otherwise, return processed input
		return new Param_Exam_Response(true, $processed_data);
	}
}