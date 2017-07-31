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

use Exception;
use Rona\Helper;
use Rona\Config\Config;
use Rona\Response;

class ParamExam {

	protected $config;

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

		// Create a config object.
		$this->config = new Config;

		$this->register_stock_config();

		$this->register_config();

		// Register the stock filters.
		$this->register_stock_filters();

		// Register filters.
		$this->register_filters();
	}

	public function config(string $item = NULL) {
		return is_null($item) ? $this->config : $this->config->get($item);
	}

	protected function register_stock_config() {

		$this->config()->set('filters.string.options.trim_full', false);
		$this->config()->set('filters.string.options.trim', ' ');
		$this->config()->set('filters.emails.options.all_match', true);
		$this->config()->set('filters.boolean.options.return_int', false);
		$this->config()->set('filters.password.options.min_length', 8);
		$this->config()->set('filters.password.options.max_length', 30);
		$this->config()->set('filters.alphanumeric.options.case', 'ci');
		$this->config()->set('filters.date.options.output_format', 'Y-m-d');
		$this->config()->set('filters.default.message.failure', function($vars) {
			return 'An invalid value was provided for this param.';
		});
		$this->config()->set('filters.string.message.is_valid', NULL);
		$this->config()->set('filters.string.message.failure', NULL);
		$this->config()->set('filters.email.message.is_valid', NULL);
		$this->config()->set('filters.email.message.failure', NULL);
		$this->config()->set('filters.emails.message.is_valid', NULL);
		$this->config()->set('filters.emails.message.failure.at_least_1', function($vars) {
			return "You must provide a valid {$vars['param']}.";
		});
		$this->config()->set('filters.emails.message.failure.all_must_match', function($vars) {
			return "You provided {$vars['num_invalids']} invalid " . Helper::pluralize($vars['param']) . ".";
		});
		$this->config()->set('filters.boolean.message.is_valid', NULL);
		$this->config()->set('filters.boolean.message.failure', NULL);
		$this->config()->set('filters.persons_name.message.is_valid', NULL);
		$this->config()->set('filters.persons_name.message.failure', NULL);
		$this->config()->set('filters.password.message.is_valid', NULL);
		$this->config()->set('filters.password.message.failure', function($vars) {
			return "The {$vars['param']} you provided is invalid. It must be between {$vars['options']['min_length']} and {$vars['options']['max_length']} characters in length.";
		});
		$this->config()->set('filters.numeric.message.is_valid', NULL);
		$this->config()->set('filters.numeric.message.failure', NULL);
		$this->config()->set('filters.alphanumeric.message.is_valid', NULL);
		$this->config()->set('filters.alphanumeric.message.failure', NULL);
		$this->config()->set('filters.date.message.is_valid', NULL);
		$this->config()->set('filters.date.message.failure', NULL);
	}

	protected function register_config() {}
	
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
		if (!$res->success && empty($res->messages))
			$res->messages = Helper::func_or($this->config('filters.default.message.failure'), get_defined_vars());

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
			if (Helper::is_nullOrEmptyString($val) && !Helper::is_nullOrEmptyString(Helper::array_get($props, 'filters.default.options')))
				$val = $props['options']['default'];

			// If dependencies were defined, then run filters only if those conditions are met
			foreach ($props['filters']['dependencies']['options'] ?? [] as $dependent_param => $dependent_val) {

				if (!Helper::array_get($input_processed, $dependent_param) === $dependent_val)
					continue 2;
			}

			// If dependent_param was declared, then proceed only if that param exists and is not null
			$dependent_param = Helper::array_get($props, 'filters.dependent_param.options');
			if (isset($dependent_param) && !isset($input_processed[$dependent_param]))
				continue;

			// If dependent_true was declared, then proceed only if that param exists, is not null, and evaluates to true
			$dependent_true = Helper::array_get($props, 'filters.dependent_true.options');
			if (isset($dependent_true) && (!isset($input_processed[$dependent_true]) || !$input_processed[$dependent_true]))
				continue;

			// If dependent_false was declared, then proceed only if that param exists, is not null, and evaluates to false
			$dependent_false = Helper::array_get($props, 'filters.dependent_false.options');
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
			if (Helper::is_emptyString($val) && Helper::array_get($props, 'filters.allow_empty_string.options'))
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
					if ($res->success)
						$val = $res->data;

					// Since this value created an error in the filter, we'll record the error message and skip any additional filters for this param
					else {

						$error_msgs[] = [
							'param'		=> $param,
							'message'	=> Helper::func_or($this->config('filters.default.message.failure'), get_defined_vars()),
							'help_text'	=> $props['help_text']
						];


						// $error_msgs = array_merge($error_msgs, $res->messages);
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

	public function register_filter(string $name, array $default_options, callable $function) {
		$this->filters[$name] = [
			'default_options'	=> $default_options,
			'function'			=> $function
		];
	}

	protected function register_stock_filters() {

		$this->register_filter('string', [
				'trim_full'		=> $this->config('filters.string.options.trim_full'), // true or false
				'trim'			=> $this->config('filters.string.options.trim') // false disables, mask will be used otherwise
			], function($val, $options) {

			if (is_string($val)) {

				if ($options['trim_full'])
					$val = Helper::trim_full($val);

				if ($options['trim'] !== false)
					$val = trim($val, $options['trim']);

				return new Response(true, Helper::func_or($this->config('filters.string.message.is_valid'), get_defined_vars()), $val);
			}

			return new Response(false, Helper::func_or($this->config('filters.string.message.failure'), get_defined_vars()));
		});

		$this->register_filter('email', [], function($val, $options) {
				
			$val = Helper::get_email($val);
			if (Helper::is_email($val))
				return new Response(true, Helper::func_or($this->config('filters.email.message.is_valid'), get_defined_vars()), $val);

			return new Response(false, Helper::func_or($this->config('filters.email.message.failure'), get_defined_vars()));
		});

		$this->register_filter('emails', [
				'all_match'		=> $this->config('filters.emails.options.all_match')
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
					return new Response(false, Helper::func_or($this->config('filters.emails.message.failure.at_least_1'), get_defined_vars()));
			}

			// If 'all_match' is set to true, then the initial count must be the same as the new count
			else {
				
				if ($refined_count != $initial_count) {
					$num_invalids = $initial_count - $refined_count;
					return new Response(false, Helper::func_or($this->config('filters.emails.message.failure.all_must_match'), get_defined_vars()));
				}
			}			
			
			return new Response(true, Helper::func_or($this->config('filters.emails.message.is_valid'), get_defined_vars()), $val);
		});

		$this->register_filter('boolean', [
				'return_int'	=> $this->config('filters.boolean.options.return_int')
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
				
				return new Response(true, Helper::func_or($this->config('filters.boolean.message.is_valid'), get_defined_vars()), $val);
			}
			
			return new Response(false, Helper::func_or($this->config('filters.boolean.message.failure'), get_defined_vars()));
		});

		$this->register_filter('persons_name', [], function($val, $options) {

			$val = Helper::trim_full($val);

			if (Helper::is_persons_name($val))
				return new Response(true, Helper::func_or($this->config('filters.persons_name.message.is_valid'), get_defined_vars()), $val);
			
			return new Response(false, Helper::func_or($this->config('filters.persons_name.message.failure'), get_defined_vars()));
		});

		$this->register_filter('password', [
			'min_length'	=> $this->config('filters.password.options.min_length'),
			'max_length'	=> $this->config('filters.password.options.max_length')
			], function($val, $options) {

			$val = trim($val);
			$pw_length = strlen($val);

			if ($pw_length >= $options['min_length'] && $pw_length <= $options['max_length'])
				return new Response(true, Helper::func_or($this->config('filters.password.message.is_valid'), get_defined_vars()), $val);
			
			return new Response(false, Helper::func_or($this->config('filters.password.message.failure'), get_defined_vars()));
		});

		$this->register_filter('numeric', [], function($val, $options) {

			$val = trim($val);

			if (Helper::is_numeric($val))
				return new Response(true, Helper::func_or($this->config('filters.numeric.message.is_valid'), get_defined_vars()), $val);
			
			return new Response(false, Helper::func_or($this->config('filters.numeric.message.failure'), get_defined_vars()));
		});

		$this->register_filter('alphanumeric', [
				'case'		=> $this->config('filters.alphanumeric.options.case')
			], function($val, $options) {

			$case = $options['case'];
			$val = trim($val);

			if (Helper::is_alphanumeric($val, $case))
				return new Response(true, Helper::func_or($this->config('filters.alphanumeric.message.is_valid'), get_defined_vars()), $val);
			
			return new Response(false, Helper::func_or($this->config('filters.alphanumeric.message.failure'), get_defined_vars()));
		});

		$this->register_filter('date', [
				'output_format'	=> $this->config('filters.date.options.output_format')
			], function($val, $options) {

			#** This function needs to be modified as it basically validates anything

			$date = date($options['output_format'], strtotime($val));

			$dt = \DateTime::createFromFormat($options['output_format'], $date);
			if ($dt !== false && !array_sum($dt->getLastErrors()))
				return new Response(true, Helper::func_or($this->config('filters.date.message.is_valid'), get_defined_vars()), $date);
			
			return new Response(false, Helper::func_or($this->config('filters.date.message.failure'), get_defined_vars()));
		});
	}

	protected function register_filters() {}
}