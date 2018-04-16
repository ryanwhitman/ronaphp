<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.0.0
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
	 * @param    bool                  $is_prepend    Prepend or append the param to the exams array.
	 * @param    \Closure|string       $param         The param name.
	 * @param    \Closure|bool         $is_reqd       Is this param required?
	 * @param    \Closure|string       $filter        The filter to apply.
	 * @param    \Closure|array        $options       Options for this param.
	 */
	protected function param_add(bool $is_prepend, $param, $is_reqd, $filter = NULL, $options = NULL) {

		// Form the param array.
		$arr = [
			'param'		=> $param,
			'is_reqd'	=> $is_reqd,
			'filter'	=> $filter,
			'options'	=> $options
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
	public function param_prepend($param, $is_reqd, $filter = NULL, $options = NULL) {
		return $this->param_add(true, $param, $is_reqd, $filter, $options);
	}

	/**
	 * Append a param.
	 *
	 * @see param_add()
	 */
	public function param_append($param, $is_reqd, $filter = NULL, $options = NULL) {
		return $this->param_add(false, $param, $is_reqd, $filter, $options);
	}

	/**
	 * Append a required param.
	 *
	 * @see param_append()
	 */
	public function reqd_param($param, $filter = NULL, $options = NULL) {
		return $this->param_append($param, true, $filter, $options);
	}

	/**
	 * Append an optional param.
	 *
	 * @see param_append()
	 */
	public function opt_param($param, $filter = NULL, $options = NULL) {
		return $this->param_append($param, false, $filter, $options);
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
			if (is_null($exam['options']))
				$exam['options'] = [];
			if (!is_array($exam['options']))
				throw new \Exception('The options argument defined in Param_Exam must be an array.');

			// Establish the initial value.
			$val = Helper::array_get($raw_data, $exam['param']);

			// Find the default value, if applicable
			if (Helper::is_null_or_empty_string($val) && !Helper::is_null_or_empty_string(Helper::array_get($exam, 'options.default')))
				$val = $exam['options']['default'];

			// If dependencies were defined, then run filter only if those conditions are met
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
					'filter'		=> $exam['filter'],
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

			// The param has a value of some sort and empty strings are disallowed, so run it through the filter, if one was defined.
			else if (!Helper::is_null_or_empty_string($exam['filter'])) {

				// Create an empty array to hold the filter that is found.
				$f = [];

				// When the filter is a string:
				if (is_string($exam['filter'])) {
					$found_filter = $this->module->get_param_filter($exam['filter']);
					if ($found_filter)
						$f = $found_filter;
				}

				// When the filter is an array with either 1 or 2 parts:
				else if (
					is_array($exam['filter']) &&
					count($exam['filter']) == 2 &&
					isset($exam['filter'][0]) &&
					isset($exam['filter'][1]) &&
					is_string($exam['filter'][1])
				) {

					// Default the found filter to false.
					$found_filter = false;

					// The filter module should be stored in index 0.
					$filter_module = $exam['filter'][0];

					// The filter name should be stored in index 1.
					$filter_name = $exam['filter'][1];

					// When the filter module is just a string, convert it to a module instance.
					if (is_string($filter_module))
						$filter_module = $this->module->get_module($filter_module);

					// If the filter module is a module instance, grab the param filter.
					if ($filter_module instanceof Module)
						$found_filter = $filter_module->get_param_filter($filter_name);

					// If a callback was found, store the filter.
					if ($found_filter)
						$f = $found_filter;
				}

				// Ensure a valid filter was found.
				if (empty($f))
					throw new \Exception('The param filter ' . json_encode($exam['filter']) . ' identified in the module "' . $this->module->get_id() . '" is not valid.');

				// Merge the option arrays
				$filter_options = array_merge($f['default_options'], $exam['options']);

				// Run the filter
				$res = $f['callback']($val, $filter_options);

				// Ensure the response is the correct type.
				if (!is_a($res, '\Rona\Param_Filter_Response'))
					throw new \Exception("The param filter did not return an instance of \Rona\Param_Filter_Response.");

				// If the response was successful, $val should be set to the response data.
				if ($res->success)
					$val = $res->data;

				// Since this value created a failure in the filter, record the fail data.
				else {
					$failed_data[$exam['param']] = [
						'val'				=> $val,
						'tag'				=> $res->tag,
						'is_reqd'			=> $exam['is_reqd'],
						'filter'			=> $exam['filter'],
						'options'			=> $exam['options'],
						'filter_options'	=> $filter_options,
						'filter_res_data'	=> $res->data
					];
					continue;
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