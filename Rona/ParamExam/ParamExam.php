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

use Exception;
use Rona\Helper\Helper as Helper;

/**
 * The main class for the ParamExam functionality.
 */
class ParamExam {
	
	/**
	 * An associative array of filters.
	 * 
	 * @var array
	 */
	protected $filters = [];

	/**
	 * The class constructor.
	 */
	public function __construct() {

		// Load the default filters.
		$this->load_default_filters();
	}

	/**
	 * Produce a response object.
	 * 
	 * @param     bool     $is_valid        Whether or not the parameter is valid.
	 * @param     string   $message         The response message explaining why the parameter is or isn't valid.
	 * @param     string   $filtered_val    The sanitized and altered value, ready for usage.
	 * @return    object                    The response object.
	 */
	protected function response($is_valid, $message = '', $filtered_val = '') {

		// Prepare and return the response object.
		$response = new \stdClass();
		$response->is_valid = (bool) $is_valid;
		$response->message = $message;
		$response->filtered_val = $filtered_val;
		return $response;
	}

	/**
	 * A short-hand method for a valid response.
	 * 
	 * @param     mixed   $filtered_val    The sanitized and altered value, ready for usage.
	 * @param     string  $message         The response message explaining why the parameter is valid.
	 * @return    object                   The response object.
	 */
	protected function res_valid($filtered_val, $message = '') {
		return $this->response(true, $message, $filtered_val);
	}

	protected function res_invalid($message = '') {
		return $this->response(false, $message);
	}

	public function config_get($string) {

		switch ($string) {

			case 'rona.filters.options.string.trim_full': return false;
			case 'rona.filters.options.string.trim': return ' ';
			case 'rona.filters.options.emails.all_match': return true;
			case 'rona.filters.options.boolean.return_int': return false;
			case 'rona.filters.options.password.min_length': return 8;
			case 'rona.filters.options.password.max_length': return 30;
			case 'rona.filters.options.alphanumeric.case': return 'ci';
			case 'rona.filters.options.date.output_format': return 'Y-m-d';
			case 'rona.filters.message.default.failure': return function($vars) {return 'An invalid value was provided for this param.';};
			case 'rona.filters.message.string.is_valid': return NULL;
			case 'rona.filters.message.string.failure': return NULL;
			case 'rona.filters.message.email.is_valid': return NULL;
			case 'rona.filters.message.email.failure': return NULL;
			case 'rona.filters.message.emails.is_valid': return NULL;
			case 'rona.filters.message.emails.failure.at_least_1': return function($vars) {return "You must provide a valid {$vars['param']}.";};
			case 'rona.filters.message.emails.failure.all_must_match': return function($vars) {return "You provided {$vars['num_invalids']} invalid " . Helper::pluralize($vars['param']) . ".";};
			case 'rona.filters.message.boolean.is_valid': return NULL;
			case 'rona.filters.message.boolean.failure': return NULL;
			case 'rona.filters.message.persons_name.is_valid': return NULL;
			case 'rona.filters.message.persons_name.failure': return NULL;
			case 'rona.filters.message.password.is_valid': return NULL;
			case 'rona.filters.message.password.failure': return function($vars) {return "The {$vars['param']} you provided is invalid. It must be between {$vars['options']['min_length']} and {$vars['options']['max_length']} characters in length.";};
			case 'rona.filters.message.numeric.is_valid': return NULL;
			case 'rona.filters.message.numeric.failure': return NULL;
			case 'rona.filters.message.alphanumeric.is_valid': return NULL;
			case 'rona.filters.message.alphanumeric.failure': return NULL;
			case 'rona.filters.message.date.is_valid': return NULL;
			case 'rona.filters.message.date.failure': return NULL;
		}
	}
	
	public function apply_filter($name, $val, $options = []) {

		// Get the filter
		$filter = Helper::array_get($this->filters, $name);

		// Ensure filter exists
		if (empty($filter))
			throw new Exception("The filter '$name' does not exist.");

		// Merge the option arrays
		$options = array_merge($filter['default_options'], $options);

		// Run the filter
		$res = $filter['function']($val, $options);

		// If the filter failed and there is no message, attach a default one
		if (!$res->is_valid && empty($res->message))
			$res->message = Helper::func_or($this->config_get('rona.filters.message.default.failure'), get_defined_vars());

		// Return the response object
		return $res;
	}

	public function examine_paramSet(ParamSet $paramSet, $unfiltered_data) {

		// Establish arrays
		$input_processed = $error_msgs = [];

		// Run through each param
		foreach ($paramSet->params as $param => $props) {

			// Establish the initial value
			$val = Helper::array_get($unfiltered_data, $param);

			// Find the default value, if applicable
			if (Helper::is_nullOrEmptyString($val) && !Helper::is_nullOrEmptyString(Helper::array_get($props, 'options.default')))
				$val = $props['options']['default'];

			// If dependencies were defined, then run filters only if those conditions are met
			foreach (Helper::array_get($props, 'options.dependencies', []) as $dependent_param => $dependent_val) {

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

			// If the param is required and the value is either null or an empty string, record the error and move to the next param
			if ($props['is_reqd'] && Helper::is_nullOrEmptyString($val)) {
				$error_msgs[] = [
					'param'		=> $param,
					'message'	=> 'This param was not provided.',
					'help_text'	=> $props['help_text']
				];
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

				foreach ($props['filters'] as $k => $v) {

					if (is_array($v)) {
						$name = $k;
						$options = $v;
					} else {
						$name = $v;
						$options = [];
					}

					$res = $this->apply_filter($name, $val, $options);
					if ($res->is_valid)
						$val = $res->filtered_val;

					// Since this value created an error in the filter, we'll record the error message and skip any additional filters for this param
					else {

						$error_msgs[] = [
							'param'		=> $param,
							'message'	=> Helper::func_or($this->config_get('rona.filters.message.default.failure'), get_defined_vars()),
							'help_text'	=> $props['help_text']
						];


						// $error_msgs = array_merge($error_msgs, $res->message);
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
			return $this->response(false, $error_msgs);

		// Otherwise, return processed input
		return $this->response(true, '', $input_processed);
	}

	public function register_filter($name, $default_options, $function) {
		$this->filters[$name] = [
			'default_options'	=> (array) $default_options,
			'function'			=> $function
		];
	}

	public function load_default_filters() {

		$this->register_filter('string', [
				'trim_full'		=> $this->config_get('rona.filters.options.string.trim_full'), // true or false
				'trim'			=> $this->config_get('rona.filters.options.string.trim') // false disables, mask will be used otherwise
			], function($val, $options) {

			if (is_string($val)) {

				if ($options['trim_full'])
					$val = Helper::trim_full($val);

				if ($options['trim'] !== false)
					$val = trim($val, $options['trim']);

				return $this->response(true, Helper::func_or($this->config_get('rona.filters.message.string.is_valid'), get_defined_vars()), $val);
			}

			return $this->response(false, Helper::func_or($this->config_get('rona.filters.message.string.failure'), get_defined_vars()));
		});

		$this->register_filter('email', [], function($val, $options) {
				
			$val = Helper::get_email($val);
			if (Helper::is_email($val))
				return $this->response(true, Helper::func_or($this->config_get('rona.filters.message.email.is_valid'), get_defined_vars()), $val);

			return $this->response(false, Helper::func_or($this->config_get('rona.filters.message.email.failure'), get_defined_vars()));
		});

		$this->register_filter('emails', [
				'all_match'		=> $this->config_get('rona.filters.options.emails.all_match')
			], function($val, $options) {
			
			// Ensure $val is an array
			$val = (array) $val;
					
			// Get the initial email address count
			$initial_count = count($val);
					
			// Reduce the email address array to only contain legitimate email addresses
			$val = Helper::get_emails($val);

			// Get the refined email address count
			$refined_count = count($val);
					
			// if 'all_match' is set to false, then ensure at least 1 legitimate email address was provided.
			if (!$options['all_match']) {
				if ($refined_count == 0)
					return $this->response(false, Helper::func_or($this->config_get('rona.filters.message.emails.failure.at_least_1'), get_defined_vars()));
			}

			// If 'all_match' is set to true, then the initial count must be the same as the new count
			else {
				
				if ($refined_count != $initial_count) {
					$num_invalids = $initial_count - $refined_count;
					return $this->response(false, Helper::func_or($this->config_get('rona.filters.message.emails.failure.all_must_match'), get_defined_vars()));
				}
			}			
			
			return $this->response(true, Helper::func_or($this->config_get('rona.filters.message.emails.is_valid'), get_defined_vars()), $val);
		});

		$this->register_filter('boolean', [
				'return_int'	=> $this->config_get('rona.filters.options.boolean.return_int')
			], function($val, $options) {

			// Convert similar inputs to a boolean
			if (
				$val === 'false' ||
				$val === 'off' ||
				$val === 'no' ||
				$val === 'n' ||
				$val === '0' ||
				$val === 0
			)
				$val = false;

			else if (
				$val === 'true' ||
				$val === 'on' ||
				$val === 'yes' ||
				$val === 'y' ||
				$val === '1' ||
				$val === 1
			)
				$val = true;
				
			if (is_bool($val)) {

				if ($options['return_int'])
					$val = $val == false ? 0 : 1;
				
				return $this->response(true, Helper::func_or($this->config_get('rona.filters.message.boolean.is_valid'), get_defined_vars()), $val);
			}
			
			return $this->response(false, Helper::func_or($this->config_get('rona.filters.message.boolean.failure'), get_defined_vars()));
		});

		$this->register_filter('persons_name', [], function($val, $options) {

			$val = Helper::trim_full($val);

			if (Helper::is_persons_name($val))
				return $this->response(true, Helper::func_or($this->config_get('rona.filters.message.persons_name.is_valid'), get_defined_vars()), $val);
			
			return $this->response(false, Helper::func_or($this->config_get('rona.filters.message.persons_name.failure'), get_defined_vars()));
		});

		$this->register_filter('password', [
			'min_length'	=> $this->config_get('rona.filters.options.password.min_length'),
			'max_length'	=> $this->config_get('rona.filters.options.password.max_length')
			], function($val, $options) {

			$val = trim($val);
			$pw_length = strlen($val);

			if ($pw_length >= $options['min_length'] && $pw_length <= $options['max_length'])
				return $this->response(true, Helper::func_or($this->config_get('rona.filters.message.password.is_valid'), get_defined_vars()), $val);
			
			return $this->response(false, Helper::func_or($this->config_get('rona.filters.message.password.failure'), get_defined_vars()));
		});

		$this->register_filter('numeric', [], function($val, $options) {

			$val = trim($val);

			if (Helper::is_numeric($val))
				return $this->response(true, Helper::func_or($this->config_get('rona.filters.message.numeric.is_valid'), get_defined_vars()), $val);
			
			return $this->response(false, Helper::func_or($this->config_get('rona.filters.message.numeric.failure'), get_defined_vars()));
		});

		$this->register_filter('alphanumeric', [
				'case'		=> $this->config_get('rona.filters.options.alphanumeric.case')
			], function($val, $options) {

			$case = $options['case'];
			$val = trim($val);

			if (Helper::is_alphanumeric($val, $case))
				return $this->response(true, Helper::func_or($this->config_get('rona.filters.message.alphanumeric.is_valid'), get_defined_vars()), $val);
			
			return $this->response(false, Helper::func_or($this->config_get('rona.filters.message.alphanumeric.failure'), get_defined_vars()));
		});

		$this->register_filter('date', [
				'output_format'	=> $this->config_get('rona.filters.options.date.output_format')
			], function($val, $options) {

			#** This function needs to be modified as it basically validates anything

			$date = date($options['output_format'], strtotime($val));

			$dt = \DateTime::createFromFormat($options['output_format'], $date);
			if ($dt !== false && !array_sum($dt->getLastErrors()))
				return $this->response(true, Helper::func_or($this->config_get('rona.filters.message.date.is_valid'), get_defined_vars()), $date);
			
			return $this->response(false, Helper::func_or($this->config_get('rona.filters.message.date.failure'), get_defined_vars()));
		});
	}
}