<?php

class Helper {

	const
		SECS_IN_DAY = 86400,
		SECS_IN_YEAR = 31536000,
		SECS_IN_SEMIYEAR = 15768000;
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public static function is_emptyString($str) {
		return is_string($str) && trim($str) === '';
	}

	public static function is_nullOrEmptyString($q) {
		return is_null($q) || self::is_emptyString($q);
	}

	public static function is_persons_name($x) {
		return preg_match('/^[a-z][a-z`\',\. ]*$/i', $x);
	}

	public static function is_numeric($x) {
		return preg_match('/^[0-9]+$/', $x);
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

	public static function is_currency($x) {
		return is_numeric($x);
	}

	public static function is_email($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	public static function are_emails($emails) {
		$emails = (array) $emails;
		foreach ($emails as $email)
			if (!self::is_email($email))
				return false;

		return true;
	}

	public static function has_length_range($str, $min, $max = '-1') {
		return strlen($str) >= $min && (strlen($str) <= $max || $max == '-1');
	}

	public static function get_email($x) {
		$email = filter_var($x, FILTER_SANITIZE_EMAIL);
		return self::is_email($email) ? $email : '';
	}

	public static function get_emails($emails) {
		$emails = (array) $emails;
		$valid_emails = array();
		foreach ($emails as $email) {
			$email = self::get_email($email);
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

	public static function array_get($array, $path, $default = NULL) {

		if (!is_array($path))
			$path = explode('.', $path);
		
		foreach ($path as $part) {
			if (!isset($array[$part]))
				return $default;

			$array = $array[$part];
		}

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

	public static function array_to_csv(array $arr, $include_space = true) {

		$csv = '';
		foreach ($arr as $v)
			$csv .= $v . ',' . ($include_space ? ' ' : '');

		return trim ($csv, ', ');
	}

	public static function possessionize($str) {
		return $str . '\'' . ($str[strlen($str) - 1] != 's' ? 's' : '');
	}

	public static function format_currency($num, $show_dollar_sign = true) {
		return ($show_dollar_sign ? '$' : '') . number_format($num, 2);
	}

	public static function method_override($http_method) {
		echo '<input type="hidden" name="_http_method" value="' . $http_method . '">';
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
			self::load_file($directory . '/' . $filename, $require, $once);
			$already_loaded[] = $filename;
		}
		
		foreach (glob($directory . '/*.php') as $filename) {
			if (in_array($filename, $already_loaded)) continue;
			self::load_file($filename, $require, $once);
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

	public static function call_func($func, $arg) {

		if (is_callable($func))
			return $func($arg);

		return call_user_func($func, $arg);
	}

}