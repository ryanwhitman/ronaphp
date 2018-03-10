<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
 */

namespace Rona;

class Param_Exam {

	/**
	 * The module this instance is running under.
	 * 
	 * @var \Rona\Module
	 */
	protected $module;

	/**
	 * The exams.
	 * 
	 * @var array
	 */
	protected $exams = [];

	/**
	 * The constructor.
	 * 
	 * @param   \Rona\Module   $module   The module this instance of Param_Exam is being run under.
	 */
	public function __construct(\Rona\Module $module) {

		// Set the module.
		$this->module = $module;
	}

	/**
	 * Add a parameter to the exams array.
	 * 
	 * @param    bool                  $is_prepend    Prepend / append param to the exams array.
	 * @param    \Closure|string       $param         The param name.
	 * @param    \Closure|bool         $is_reqd       Is this param required?
	 * @param    \Closure|array        $filters       The filters to apply.
	 * @param    \Closure|array        $options       Options for this param.
	 */
	protected function param_add(bool $is_prepend, $param, $is_reqd, $filters = [], $options = []) {

		// Form the param array.
		$arr = [
			'param'			=> $param,
			'is_reqd'		=> $is_reqd,
			'filters'		=> $filters,
			'options'		=> $options
		];

		// Add this param to the exams array.
		if ($is_prepend)
			array_unshift($this->exams, $arr);
		else
			$this->exams[] = $arr;
	}

	/**
	 * Prepend a param.
	 *
	 * @see param_add()
	 */
	public function param_prepend($param, $is_reqd, $filters = [], $options = []) {
		return $this->param_add(true, $param, $is_reqd, $filters, $options);
	}

	/**
	 * Append a param.
	 *
	 * @see param_add()
	 */
	public function param_append($param, $is_reqd, $filters = [], $options = []) {
		return $this->param_add(false, $param, $is_reqd, $filters, $options);
	}

	/**
	 * Append a required param.
	 *
	 * @see param_append()
	 */
	public function reqd_param($param, $filters = [], $options = []) {
		return $this->param_append($param, true, $filters, $options);
	}

	/**
	 * Append an optional param.
	 *
	 * @see param_append()
	 */
	public function opt_param($param, $filters = [], $options = []) {
		return $this->param_append($param, false, $filters, $options);
	}

	/**
	 * Append a callback function to the exams array.
	 * 
	 * @param  \Closure    $callback
	 */
	public function callback(\Closure $callback) {
		$this->exams[] = $callback;
	}

	/**
	 * Examine data.
	 * 
	 * @param  array  $raw_data  The data to examine.
	 */
	public function examine(array $raw_data = []): Param_Exam_Response {

		// Create an array to hold the successful data.
		$successful_data = [];

		// Create an array to hold the failed data.
		$failed_data = [];

		// Run thru the exams. Using an infinite loop allows exams to be dynamically added with the callback methods.
		while (1) {

			// If no more exams exist, break the infinite loop.
			if (empty($this->exams))
				break;

			// Establish the current exam.
			$exam = $this->exams[0];

			// Remove the current exam from the exams array so that it doesn't get processed again.
			unset($this->exams[0]);

			// Re-index the exams array so that the index starts at 0.
			$this->exams = array_values($this->exams);

			// The exam may just be a closure.
			if ($exam instanceof \Closure) {
				$exam(empty($failed_data), $raw_data, $successful_data, $failed_data);
				continue;
			}

			// The exam arguments can be passed in as a closure.
			foreach ($exam as $arg => $val)
				$exam[$arg] = Helper::maybe_closure($val, empty($failed_data), $raw_data, $successful_data, $failed_data);

			// Ensure the exam arguments are of the correct type. Some args can be defaulted.
			if (!is_string($exam['param']))
				throw new \Exception('The param argument defined in Param_Exam must be a string.');
			if (!is_bool($exam['is_reqd']))
				throw new \Exception('The is_reqd argument defined in Param_Exam must be a boolean.');
			if (is_null($exam['filters']))
				$exam['filters'] = [];
			if (!is_array($exam['filters']))
				throw new \Exception('The filters argument defined in Param_Exam must be an array.');
			if (is_null($exam['options']))
				$exam['options'] = [];
			if (!is_array($exam['options']))
				throw new \Exception('The options argument defined in Param_Exam must be an array.');

			// Establish the initial value.
			$val = Helper::array_get($raw_data, $exam['param']);

			// Find the default value, if applicable
			if (Helper::is_null_or_empty_string($val) && !Helper::is_null_or_empty_string(Helper::array_get($exam, 'options.default')))
				$val = $exam['options']['default'];

			// If dependencies were defined, then run filters only if those conditions are met
			foreach ($exam['options']['dependencies'] ?? [] as $dependent_param => $dependent_val) {
				if (!Helper::array_get($successful_data, $dependent_param) === $dependent_val)
					continue 2;
			}

			// If dependent_param was declared, then proceed only if that param exists and is not null
			$dependent_param = Helper::array_get($exam, 'options.dependent_param');
			if (isset($dependent_param) && !isset($successful_data[$dependent_param]))
				continue;

			// If dependent_true was declared, then proceed only if that param exists, is not null, and evaluates to true
			$dependent_true = Helper::array_get($exam, 'options.dependent_true');
			if (isset($dependent_true) && (!isset($successful_data[$dependent_true]) || !$successful_data[$dependent_true]))
				continue;

			// If dependent_false was declared, then proceed only if that param exists, is not null, and evaluates to false
			$dependent_false = Helper::array_get($exam, 'options.dependent_false');
			if (isset($dependent_false) && (!isset($successful_data[$dependent_false]) || $successful_data[$dependent_false]))
				continue;

			// If the param is required and the value is either null or an empty string, record the fail data and move to the next param.
			if ($exam['is_reqd'] && Helper::is_null_or_empty_string($val)) {
				$failed_data[$exam['param']] = [
					'val'			=> $val,
					'tag'			=> 'non_existent',
					'is_reqd'		=> $exam['is_reqd'],
					'filters'		=> $exam['filters'],
					'options'		=> $exam['options']
				];
				continue;
			}

			// If the param is null, skip it since it passed the 'required check' above.
			if (is_null($val))
				continue;

			// If the param is just an empty string and the "allow empty string" option was set, just trim it and leave it be
			if (Helper::is_empty_string($val) && Helper::array_get($exam, 'options.allow_empty_string'))
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

				foreach ($exam['filters'] as $filter) {

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
						$failed_data[$exam['param']] = [
							'val'				=> $val,
							'tag'				=> $res->tag,
							'is_reqd'			=> $exam['is_reqd'],
							'filters'			=> $exam['filters'],
							'options'			=> $exam['options'],
							'filter_options'	=> $filter_options,
							'filter_res_data'	=> $res->data
						];
						continue 2;
					}
				}
			}

			// Save the value into the successful_data array
			Helper::array_set(
				$successful_data,
				trim(
					($exam['options']['to_array'] ?? '') . '.' . (!Helper::is_null_or_empty_string($exam['options']['to_param'] ?? '') ? $exam['options']['to_param'] : $exam['param']),
					'. '
				),
				$val
			);
		}
		
		// If there are fail data, return them.
		if (!empty($failed_data))
			return new Param_Exam_Response(false, $failed_data);

		// Otherwise, return successful data.
		return new Param_Exam_Response(true, $successful_data);
	}
}