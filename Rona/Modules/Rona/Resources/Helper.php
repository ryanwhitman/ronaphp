<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.6.0
 */

namespace Rona\Modules\Rona\Resources;

/**
 * A general helper resource.
 */
class Helper {

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Get the value that's associated with the provided array key. The array key can belong to an indexed array, an associative array, or a multidimensional array. If the provided key does not exist, the $default value is returned instead. This method allows the developer to bypass "isset()."
	 *
	 * @param     array           $array       The array to search.
	 * @param     array|string    $path        A key, multiple keys in the format of an array, or multiple keys in the format of a string separated by a period(.).
	 * @param     mixed           $default     The value to return when the key fails the "isset()" test.
	 * @return    mixed                        The value that's associated with the provided key or the $default value when that key fails the "isset()" test.
	 */
	public function array_get($array, $path, $default = NULL) {

		// If the path is not already an array, it's assumed to be a string in which the dot is the delimiter for array items.
		if (!is_array($path))
			$path = explode('.', $path);

		// Loop through each of the path parts. Keep going until either the array key is not found (return default) or the array loop has completed.
		foreach ($path as $part) {
			if (!array_key_exists($part, $array))
				return $default;
			$array = $array[$part];
		}

		// The array key must have been found. Return the value.
		return $array;
	}

	public function array_set(&$array, $path, $val) {

		if (!is_array($path))
			$path = explode('.', $path);

		$set_array = &$array;
		foreach ($path as $part)
			$set_array = &$set_array[$part];

		$set_array = $val;
	}

	public function is_empty_string($str): bool {
		return is_string($str) && trim($str) === '';
	}

	public function is_null_or_empty_string($q): bool {
		return is_null($q) || $this->is_empty_string($q);
	}

	public function has_length_range($str, $min, $max = '-1'): bool {
		return strlen($str) >= $min && (strlen($str) <= $max || $max == '-1');
	}

	public function trim_full($str) {
		$str = trim($str);
		$str = preg_replace('/[ ]+(\s)/', '$1', $str);
		$str = preg_replace('/(\s)[ ]+/', '$1', $str);
		$str = preg_replace('/\0/', '', $str);
		$str = preg_replace('/(\n){2,}/', '$1$1', $str);
		$str = preg_replace('/\f/', '', $str);
		$str = preg_replace('/(\r){2,}/', '$1$1', $str);
		$str = preg_replace('/\t/', '', $str);
		$str = preg_replace('/(\v){2,}/', '$1$1', $str);
		return $str;
	}

	public function get_random($pattern, $len) {

		$numbers = '0123456789';
		$letters = 'abcdefghijklmnopqrstuvwxyz';

		switch ($pattern) {

			case 'num':
				$chars = $numbers;
			break;

			case 'let':
				$chars = strtolower($letters) . strtoupper($letters);
			break;

			case 'let_lc':
				$chars = strtolower($letters);
			break;

			case 'let_uc':
				$chars = strtoupper($letters);
			break;

			case 'alphanum':
				$chars = strtolower($letters) . strtoupper($letters) . $numbers;
			break;

			case 'alphanum_lc':
				$chars = strtolower($letters) . $numbers;
			break;

			case 'alphanum_uc':
				$chars = strtoupper($letters) . $numbers;
			break;

			default:
				return false;
		}

		$random_str = '';
		for ($i = 0; $i < $len; $i++)
			$random_str .= $chars[rand(0, strlen($chars) -1)];

		return $random_str;
	}

	public function begins_with(string $haystack, string $needle): bool {
		$length = strlen($needle);
		return substr($haystack, 0, $length) === $needle;
	}

	public function ends_with(string $haystack, string $needle): bool {
		$length = strlen($needle);
		if ($length == 0)
			return true;
		return substr($haystack, -$length) === $needle;
	}

	/**
	 * Remove text from the beginning of a string.
	 *
	 * @param  string   $text_to_remove   The text that is to be removed.
	 * @param  string   $str_to_edit      The string that is to have the text removed from it.
	 * @return string                     The modified string that has had the text removed from the beginning of it.
	 */
	public function remove_text_from_beginning(string $text_to_remove, string $str_to_edit): string {
		if (substr($str_to_edit, 0, strlen($text_to_remove)) == $text_to_remove)
			$str_to_edit = substr($str_to_edit, strlen($text_to_remove));
		return $str_to_edit;
	}

	public function contains_only_digits($x): bool {
		return is_int($x) || (is_string($x) && preg_match('/^[0-9]+$/', $x));
	}

	public function is_alphanumeric($x, $case = 'ci'): bool {
		if ($case == 'ci')
			return preg_match('/^[a-z0-9]+$/i', $x);
		else if ($case == 'lc')
			return preg_match('/^[a-z0-9]+$/', $x);
		else if ($case == 'uc')
			return preg_match('/^[A-Z0-9]+$/', $x);
		return false;
	}

	public function is_email($email): bool {
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	public function are_emails($emails): bool {
		$emails = (array) $emails;
		foreach ($emails as $email)
			if (!$this->is_email($email))
				return false;
		return true;
	}

	public function get_email($x) {
		$email = filter_var($x, FILTER_SANITIZE_EMAIL);
		return $this->is_email($email) ? $email : '';
	}

	public function get_emails($emails) {
		$emails = (array) $emails;
		$valid_emails = array();
		foreach ($emails as $email) {
			$email = $this->get_email($email);
			if (!empty($email))
				$valid_emails[] = $email;
		}
		return $valid_emails;
	}

	public function is_persons_name($x): bool {
		return preg_match('/^[a-z][a-z`\',\.\- ]*$/i', $x);
	}

	/**
	 * Determine whether or not a value is a timestamp.
	 *
	 * @param    mixed      $val   The value to evaluate.
	 * @return   boolean
	 */
	public function is_timestamp($val): bool {
		return (((is_int($val) || is_float($val)) ? $val : (string) (int) $val) === $val) && ((int) $val <= PHP_INT_MAX) && ((int) $val >= ~PHP_INT_MAX);
	}

	/**
	 * Determine whether or not the value is a valid currency.
	 *
	 * @param   mixed     $val                     The value to evaluate.
	 * @param   bool      $allow_negative          Whether or not a negative currency should be allowed.
	 * @param   bool      $must_have_2_decimals    Whether or not 2 decimals are required.
	 * @return  bool
	 */
	public function is_currency($val, bool $allow_negative = true, bool $must_have_2_decimals = true): bool {
		return preg_match('/^' . ($allow_negative ? '-?' : '') . '[0-9]+(?:\.[0-9]{' . ($must_have_2_decimals ? '2' : '1,') . '})' . ($must_have_2_decimals ? '' : '?') . '$/', $val) ? true : false;
	}

	public function maybe_closure($maybe_closure, ...$args) {
		return $maybe_closure instanceof \Closure ? call_user_func_array($maybe_closure, $args) : $maybe_closure;
	}

	public function method_override($method) {
		echo '<input type="hidden" name="_method" value="' . $method . '">';
	}

	public function is_failed_input($item): bool {
		return is_a($item, '\Rona\Param_Exam_Response') && !$item->success;
	}
}