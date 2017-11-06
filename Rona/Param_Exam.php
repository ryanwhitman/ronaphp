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
use Rona\Helper;

class Param_Exam {

	protected $module;

	protected $params = [];

	public function __construct(\Rona\Module $module) {

		// Set the module.
		$this->module = $module;
	}
	
	public function param(string $param, bool $is_reqd, array $props = []): self {

		// Validate and default the help_text property.
		if (array_key_exists('help_text', $props)) {
			if (!is_string($props['help_text']))
				throw new \Exception("The 'help_text' property for params must be a string.");
		} else
			$props['help_text'] = '';

		// Validate and default the fail_message property.
		if (array_key_exists('fail_message', $props)) {
			if (!is_string($props['fail_message']) && !$props['fail_message'] instanceof \Closure)
				throw new \Exception("The 'fail_message' property for params must be either a string or anonymous function.");
		} else
			$props['fail_message'] = '';

		// Validate and default the filters property.
		if (array_key_exists('filters', $props)) {
			if (!is_array($props['filters']))
				throw new \Exception("The 'filters' property for params must be an array.");
		} else
			$props['filters'] = [];

		// Validate and default the options property.
		if (array_key_exists('options', $props)) {
			if (!is_array($props['options']))
				throw new \Exception("The 'options' property for params must be an array.");
		} else
			$props['options'] = [];

		// Store the param and its properties.
		$this->params[$param] = [
			'is_reqd'		=> $is_reqd,
			'fail_message'	=> $props['fail_message'],
			'filters'		=> $props['filters'],
			'options'		=> $props['options']
		];

		// Return this object.
		return $this;
	}
	
	public function reqd_param(string $param, array $props = []): self {

		return $this->param($param, true, $props);
	}
	
	public function opt_param(string $param, array $props = []): self {

		return $this->param($param, false, $props);
	}

	public function examine($unfiltered_data) {

		// Establish arrays
		$input_processed = $error_msgs = [];

		// Run through each param
		foreach ($this->params as $param => $props) {

			// Establish the initial value
			$val = Helper::array_get($unfiltered_data, $param);

			// Find the default value, if applicable
			if (Helper::is_nullOrEmptyString($val) && !Helper::is_nullOrEmptyString(Helper::array_get($props, 'options.default')))
				$val = $props['options']['default'];

			// If dependencies were defined, then run filters only if those conditions are met
			foreach ($props['options']['dependencies'] ?? [] as $dependent_param => $dependent_val) {

				if (!Helper::array_get($input_processed, $dependent_param) === $dependent_val)
					continue 2;
			}

			// If dependent_param was declared, then proceed only if that param exists and is not null
			$dependent_param = Helper::array_get($props, 'options.dependent_param');
			if (isset($dependent_param) && !isset($input_processed[$dependent_param]))
				continue;

			// If dependent_true was declared, then proceed only if that param exists, is not null, and evaluates to true
			$dependent_true = Helper::array_get($props, 'options.dependent_true');
			if (isset($dependent_true) && (!isset($input_processed[$dependent_true]) || !$input_processed[$dependent_true]))
				continue;

			// If dependent_false was declared, then proceed only if that param exists, is not null, and evaluates to false
			$dependent_false = Helper::array_get($props, 'options.dependent_false');
			if (isset($dependent_false) && (!isset($input_processed[$dependent_false]) || $input_processed[$dependent_false]))
				continue;

			// If the param is required and the value is either null or an empty string, record the error message and move to the next param.
			if ($props['is_reqd'] && Helper::is_nullOrEmptyString($val)) {
				$error_msgs[] = (string) Helper::func_or($props['fail_message'], [
					'fail_tag'		=> 'null_or_empty_string',
					'param'			=> $param,
					'props'			=> $props
				]);
				continue;
			}

			// If the param is null, then we just want to skip it since it passed the 'required check' above
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

					// Ensure the response is a \Rona\Response object.
					if (!is_a($res, '\Rona\Response'))
						throw new \Exception("Param filters must return a valid response object.");

					// If the response was successful, $val should be set to the response data.
					if ($res->success)
						$val = $res->data;

					// Since this value created an error in the filter, we'll record the error message and skip any additional filters for this param.
					else {
						$fail_tag = $res->data['fail_tag'] ?? '';
						unset($res->data['fail_tag']);
						$error_msgs[] = (string) Helper::func_or($props['fail_message'], [
							'fail_tag'			=> $fail_tag,
							'param'				=> $param,
							'filter_options'	=> $filter_options,
							'filter_res_data'	=> $res->data
						]);

						continue 2;
					}
				}
			}

			// Save the value into the $input_processed array
			$to_param = Helper::array_get($props['options'], 'to_param');
			$param = !Helper::is_nullOrEmptyString($to_param) ? $to_param : $param;

			$path = Helper::array_get($props['options'], 'to_array') . '.' . $param;
			$path = trim($path, '. ');

			Helper::array_set($input_processed, $path, $val);
		}
			
		// If there are any error messages, return them
		if (!empty($error_msgs))
			return new Response(false, $error_msgs);

		// Otherwise, return processed input
		return new Response(true, '', $input_processed);
	}
}