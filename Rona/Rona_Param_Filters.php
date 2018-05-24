<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.2.0
 */

namespace Rona;

use Rona\Helper;

/**
 * General Param Filter Group
 */
class Rona_Param_Filters extends Param_Filter_Group {

	/**
	 * @see parent class
	 */
	protected function register_filters() {

		/**
		 * Boolean
		 */
		$this->register('boolean', [
				'return_int'	=> false
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

					return $this->valid($val);
				}

				return $this->invalid('invalid_boolean');
			}
		);

		/**
		 * Integer
		 */
		$this->register('integer', [
			'min'	=> false,
			'max'	=> false
		], function($val, $options) {

			// Trim the value.
			$val = trim($val);

			// Ensure value only contains digits.
			if (Helper::contains_only_digits($val)) {

				// Cast the value as an integer.
				$val = (int) $val;

				// Ensure value is of the correct min/max limits.
				if (($options['min'] === false || $val >= $options['min']) && ($options['max'] === false || $val <= $options['max']))
					return $this->valid($val);
			}

			// The value is not a valid integer.
			return $this->invalid('invalid_integer');
		});

		/**
		 * String
		 */
		$this->register('string', [
				'trim_full'		=> false, // true or false
				'trim'			=> ' ' // false disables, mask will be used otherwise
			], function($val, $options) {

				if (is_string($val)) {

					if ($options['trim_full'])
						$val = Helper::trim_full($val);

					if ($options['trim'] !== false)
						$val = trim($val, $options['trim']);

					return $this->valid($val);
				}

				return $this->invalid('invalid_string');
			}
		);

		/**
		 * Array
		 */
		$this->register('array', [], function($val, $options) {

			// The value must be an array.
			if (is_array($val))
				return $this->valid($val);

			// The value is not an array.
			return $this->invalid('not_array');
		});

		/**
		 * Digits
		 */
		$this->register('digits', [], function($val, $options) {

			$val = trim($val);

			if (Helper::contains_only_digits($val))
				return $this->valid($val);

			return $this->invalid('contains_non_digits');
		});

		/**
		 * Alphanumeric
		 */
		$this->register('alphanumeric', [
				'case'		=> 'ci'
			], function($val, $options) {
				$val = trim($val);
				if (Helper::is_alphanumeric($val, $options['case']))
					return $this->valid($val);
				return $this->invalid('not_alphanumeric');
			}
		);

		/**
		 * Timestamp
		 */
		$this->register('timestamp', [
				'output_format'	=> 'Y-m-d H:i:s'
			], function($val, $options) {

				// Validate the value as a timestamp and return it in the correct format.
				return Helper::is_timestamp($val) ? $this->valid(date($options['output_format'], $val)) : $this->invalid('invalid_timestamp');
			}
		);

		/**
		 * Date
		 */
		$this->register('date', [
				'output_format'	=> 'Y-m-d'
			], function($val, $options) {

				#** This filter needs to be modified as it basically validates anything

				$val = date($options['output_format'], strtotime($val));

				$dt = \DateTime::createFromFormat($options['output_format'], $val);
				if ($dt !== false && !array_sum($dt->getLastErrors()))
					return $this->valid($val);

				return $this->invalid('invalid_date');
			}
		);

		/**
		 * Year
		 */
		$this->register('year', [
			'length'	=> 4,
			'floor'		=> false,
			'ceiling'	=> false
		], function($val, $options) {

			// Trim the value.
			$val = trim($val);

			// Ensure the value only contains digits, is of the correct length, and meets the floor/ceiling requirements.
			if (
				Helper::contains_only_digits($val) &&
				strlen($val) == $options['length'] &&
				(
					$options['floor'] === false ||
					$val >= $options['floor']
				) &&
				(
					$options['ceiling'] === false ||
					$val <= $options['ceiling']
				)
			) {
				return $this->valid($val);
			}

			// The value is not a valid year.
			return $this->invalid('invalid_year');
		});

		/**
		 * URL
		 */
		$this->register('url', [
		    'validate_flag'	=> NULL
		], function($val, $options) {

			// Prepare the value.
		    $val = trim(filter_var($val, FILTER_SANITIZE_URL));

		    // Validate the URL.
		    $res = filter_var($val, FILTER_VALIDATE_URL, $options['validate_flag']);

		    // Response
		    return $res ? $this->valid($val) : $this->invalid('not_url');
		});

		/**
		 * Currency
		 */
		$this->register('currency', [
			'min'				=> false,
			'max'				=> false,
			'allow_negative'	=> true
		], function($val, $options) {

			// Trim the value.
			$val = trim($val);

			// Strip the value of a leading dollar sign.
			$val = Helper::remove_text_from_beginning('$', $val);

			// Trim the value again.
			$val = trim($val);

			// Ensure the value is a currency.
			if (Helper::is_currency($val, $options['allow_negative'], false)) {

				// Convert the value to a float and round it to 2 decimals.
				$val = round((float) $val, 2);

				// Ensure the value is still a currency and ensure the value is of the correct min/max limits.
				if (
					Helper::is_currency($val, $options['allow_negative'], false) &&
					($options['min'] === false || $val >= $options['min']) &&
					($options['max'] === false || $val <= $options['max'])
				) {
					return $this->valid($val);
				}
			}

			// The value is not a valid currency.
			return $this->invalid('invalid_currency');
		});

		/**
		 * Email address
		 */
		$this->register('email', [], function($val, $options) {

			$val = Helper::get_email($val);
			if (Helper::is_email($val))
				return $this->valid($val);

			return $this->invalid('invalid_email');
		});

		/**
		 * Multiple email addresses
		 */
		$this->register('emails', [
				'all_match'		=> true
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
						return $this->invalid('no_emails');
				}

				// If 'all_match' is set to true, then the initial count must be the same as the new count
				else {

					if ($refined_count != $initial_count) {
						$num_invalids = $initial_count - $refined_count;
						return $this->invalid('all_emails_invalid');
					}
				}

				return $this->valid($val);
			}
		);

		/**
		 * Password
		 */
		$this->register('password', [
			'min_length'	=> 8,
			'max_length'	=> false
			], function($val, $options) {

				$val = trim($val);
				$pw_length = strlen($val);

				if ($pw_length >= $options['min_length'] && (!$options['max_length'] || $pw_length <= $options['max_length']))
					return $this->valid($val);

				return $this->invalid('invalid_length');
			}
		);

		/**
		 * Person's name
		 */
		$this->register('persons_name', [], function($val, $options) {

			$val = Helper::trim_full($val);

			if (Helper::is_persons_name($val))
				return $this->valid($val);

			return $this->invalid('invalid_persons_name');
		});
	}
}