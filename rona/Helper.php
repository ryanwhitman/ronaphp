<?php

class Helper {

	const
		SECS_IN_DAY = 86400,
		SECS_IN_YEAR = 31536000,
		SECS_IN_SEMIYEAR = 15768000;
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}
				
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

	public static function is_numeric($x) {
		return preg_match('/^[0-9]+$/', $x);
	}

	public static function is_alphanumeric_ci($x) {
		return preg_match('/^[a-z0-9]+$/i', $x);
	}

	public static function has_length_range($min, $max, $str) {
		if (strlen($str) >= $min) {
			if (strlen($str) <= $max || $max == '-1') {
				return true;
			}
		}
		
		return false;
	}

	public static function get_email($x) {
		$email = filter_var($x, FILTER_SANITIZE_EMAIL);
		return self::is_email($email) ? $email : '';
	}

	public static function get_emails($x) {
		$x = (array) $x;
		$emails = array();
		foreach ($x as $email) {
			$email = self::get_email($email);
			if (!empty($email)) {
				$emails[] = $email;
			}
		}
		return $emails;
	}

	public static function is_email($x) {
		return filter_var($x, FILTER_VALIDATE_EMAIL);
	}

	public static function are_emails($x) {
		$x = (array) $x;
		$are_emails = true;
		foreach ($x as $email) {
			if (!self::is_email($email)) {
				$are_emails = false;
			}
		}
		return $are_emails;
	}

	public static function get_currency($x) {
		$currency = preg_replace('/^[\$]/', '', $x);
		$currency = str_replace(',', '', $currency);
		if (is_numeric($currency)) {
			$currency = round($currency, 2);
			if ($currency >= -999999999 && $currency <= 999999999) {
				return $currency;
			}
		}
		return NULL;
	}

	public static function is_currency($x) {
		return is_numeric($x);
	}

	public static function is_persons_name($x) {
		return preg_match('/^[a-z][a-z`\',\. ]*$/i', $x);
	}

	public static function trim_full($x) {
		$x = trim($x);
		$x = preg_replace('/[ ]+(\s)/', '$1', $x);
		$x = preg_replace('/(\s)[ ]+/', '$1', $x);
		$x = preg_replace('/\0/', '', $x);
		$x = preg_replace('/(\n){2,}/', '$1$1', $x);
		$x = preg_replace('/\f/', '', $x);
		$x = preg_replace('/(\r){2,}/', '$1$1', $x);
		$x = preg_replace('/\t/', '', $x);
		$x = preg_replace('/(\v){2,}/', '$1$1', $x);
		return $x;
	}

	public static function get_random($type, $len) {

		$numbers = '0123456789';
		$letters_lc = 'abcdefghijklmnopqrstuvwxyz';
		$letters_uc = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

		switch ($type) {
		
			case 'numbers':
				$chars = $numbers;
			break;
		
			case 'letters':
				$chars = $letters_lc . $letters_uc;
			break;
		
			case 'string':
				$chars = $letters_lc . $letters_uc . $numbers;
			break;
			
			default:
				return false;
		}
		
		$ret = '';
		for ($i = 0; $i < $len; $i++) {
			$ret .= $chars[rand(0, strlen($chars) -1)];
		}
		return $ret;
	}

	public static function get_date($x) {
		return date('Y-m-d', strtotime($x));
	}

	public static function format_currency($num, $show_dollar_sign = true) {
		return ($show_dollar_sign ? '$' : '') . number_format($num, 2);
	}

	public static function get(&$var, $default = NULL) {
		return isset($var) ? $var : $default;
	}

	public static function array_get($array, $keys, $default = NULL) {
		
		$keys = explode('.', $keys);
		foreach ($keys as $key) {
			if (!isset($array[$key]))
				return $default;

			$array = $array[$key];
		}

		return $array;
	}

	public static function possessionize($str) {
		return $str . '\'' . ($str[strlen($str) - 1] != 's' ? 's' : '');
	}

	public static function method_override($http_method) {
		echo '<input type="hidden" name="_http_method" value="' . $http_method . '">';
	}

}

?>