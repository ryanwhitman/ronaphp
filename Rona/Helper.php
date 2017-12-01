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

abstract class Helper {

	public static $secs_in_day = 86400;

	public static $secs_in_year = 31536000;

	public static $secs_in_semiyear = 15768000;

	/**
	 * Get the value that's associated with the provided array key. The array key can belong to an indexed array, an associative array, or a multidimensional array. If the provided key does not exist, the $default value is returned instead. This method allows the developer to bypass "isset()."
	 * 
	 * @param     array           $array       The array to search.
	 * @param     array|string    $path        A key, multiple keys in the format of an array, or multiple keys in the format of a string separated by a period(.).
	 * @param     mixed           $default     The value to return when the key fails the "isset()" test.
	 * @return    mixed                        The value that's associated with the provided key or the $default value when that key fails the "isset()" test.
	 */
	public static function array_get($array, $path, $default = NULL) {

		// If the path is not already an array, it's assumed to be a string in which the dot is the delimiter for array items.
		if (!is_array($path))
			$path = explode('.', $path);
		
		// Loop thru each of the path parts. Keep going until either the array key is not found (return default) or the array loop has completed.
		foreach ($path as $part) {
			if (!array_key_exists($part, $array))
				return $default;
			$array = $array[$part];
		}

		// The array key must have been found. Return the value.
		return $array;
	}

	public static function array_set(&$array, $path, $val) {

		if (!is_array($path))
			$path = explode('.', $path);

		$set_array = &$array;
		foreach ($path as $part)
			$set_array = &$set_array[$part];

		$set_array = $val;
	}

	public static function maybe_closure($maybe_closure, ...$args) {

		return $maybe_closure instanceof \Closure ? call_user_func_array($maybe_closure, $args) : $maybe_closure;
	}

	public static function pluralize($word) {

		switch (substr($word, -0, 1)) {

			case 'e':
				return $word . 's';
				break;

			default:
				return $word . 'es';
		}
	}

	public static function is_emptyString($str) {
		return is_string($str) && trim($str) === '';
	}

	public static function is_nullOrEmptyString($q) {
		return is_null($q) || static::is_emptyString($q);
	}

	public static function indefinite_article($word, $output_word = true) {
		$indefinite_article = in_array(strtolower(substr($word, 0, 1)), ['a', 'e', 'i', 'o', 'u']) ? 'an' : 'a';
		return $indefinite_article . ($output_word ? ' ' . $word : '');
	}

	public static function trim_full($str) {
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

	public static function is_persons_name($x) {
		return preg_match('/^[a-z][a-z`\',\.\- ]*$/i', $x);
	}

	public static function is_numeric($x) {
		return is_string($x) && preg_match('/^[0-9]+$/', $x);
	}

	public static function is_alphanumeric($x, $case = 'ci') {

		if ($case == 'ci')
			return preg_match('/^[a-z0-9]+$/i', $x);
		else if ($case == 'lc')
			return preg_match('/^[a-z0-9]+$/', $x);
		else if ($case == 'uc')
			return preg_match('/^[A-Z0-9]+$/', $x);

		return false;
	}

	public static function is_email($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	public static function are_emails($emails) {
		$emails = (array) $emails;
		foreach ($emails as $email)
			if (!static::is_email($email))
				return false;

		return true;
	}

	public static function has_length_range($str, $min, $max = '-1') {
		return strlen($str) >= $min && (strlen($str) <= $max || $max == '-1');
	}

	public static function get_email($x) {
		$email = filter_var($x, FILTER_SANITIZE_EMAIL);
		return static::is_email($email) ? $email : '';
	}

	public static function get_emails($emails) {
		$emails = (array) $emails;
		$valid_emails = array();
		foreach ($emails as $email) {
			$email = static::get_email($email);
			if (!empty($email))
				$valid_emails[] = $email;
		}

		return $valid_emails;
	}

	public static function get_currency($x) {
		$currency = preg_replace('/^[\$]/', '', $x);
		$currency = str_replace(',', '', $currency);
		if (is_numeric($currency)) {
			$currency = round($currency, 2);
			if ($currency >= -999999999 && $currency <= 999999999)
				return $currency;
		}
		return NULL;
	}

	public static function get_date($x) {
		return date('Y-m-d', strtotime($x));
	}

	public static function get_random($pattern, $len) {

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

	public static function array_vals_to_list(array $arr, $delimiter = ', ') {

		$list = '';
		foreach ($arr as $v)
			$list .= $v . $delimiter;

		return trim($list, $delimiter);
	}

	public static function possessionize($str) {
		return $str . '\'' . ($str[strlen($str) - 1] != 's' ? 's' : '');
	}

	public static function format_currency($num, $show_dollar_sign = true) {
		return ($show_dollar_sign ? '$' : '') . number_format($num, 2);
	}

	public static function method_override($method) {
		echo '<input type="hidden" name="_method" value="' . $method . '">';
	}
				
	public static function load_file($file, $require = true, $once = true) {
		if ($require)
			if ($once)
				require_once $file;
			else
				require $file;
		else
			if ($once)
				include_once $file;
			else
				include $file;
	}

	public static function load_directory($directory, $require = true, $once = true, $precedence = []) {

		$precedence = (array) $precedence;
		$already_loaded = [];
		foreach ($precedence as $file) {
			$filename = $file . '.php';
			static::load_file($directory . '/' . $filename, $require, $once);
			$already_loaded[] = $filename;
		}
		
		foreach (glob($directory . '/*.php') as $filename) {
			if (in_array($filename, $already_loaded)) continue;
			static::load_file($filename, $require, $once);
		}
	}

	public static function location($base, $query_params = []) {

		$query_string = '';
		if (!empty($query_params) && is_array($query_params)) {
			$query_string = '?';
			foreach ($query_params as $k => $v)
				$query_string .= $k . '=' . $v . '&';
			
			$query_string = trim($query_string, '&');
		}

		header('Location: ' . $base . $query_string);
		exit;
	}

	public static function begins_with(string $haystack, string $needle): bool {
		$length = strlen($needle);
		return substr($haystack, 0, $length) === $needle;
	}

	public static function ends_with(string $haystack, string $needle): bool {
		$length = strlen($needle);
		if ($length == 0)
			return true;
		return substr($haystack, -$length) === $needle;
	}

	public static function is_failed_input($item): bool {
		return is_a($item, '\Rona\Param_Exam_Response') && !$item->success;
	}
}