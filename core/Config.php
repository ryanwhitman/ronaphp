<?php

define('RONA_UNDEFINED', 'RONA__UNDEFINED__RONA');

require_once __DIR__ . '/Config_.php';

class Config {

	private static $instance;

	private
		$constants = [],
		$variables = [];
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	private static function instance() {

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}

	public static function define($path, $val = RONA_UNDEFINED) {

		# Used for constants

		return self::m($path, $val, true);
	}

	public static function set($path, $val = RONA_UNDEFINED) {

		# Used for variables

		return self::m($path, $val, false);
	}

	public static function m($path, $val = RONA_UNDEFINED, $is_const) {

		$path = strtolower(trim($path, ' .'));

		$path_buildup = '';
		foreach (explode('.', $path) as $part) {
			$path_buildup .= '.' . $part;
			$path_buildup = trim($path_buildup, ' .');
			$eval_arr = Helper::array_get(self::instance()->constants, $path_buildup, RONA_UNDEFINED);
			if ($eval_arr !== RONA_UNDEFINED && !is_array($eval_arr))
				return false;
		}

		if ($val === RONA_UNDEFINED)
			return new Config_($path, $is_const);

		if ($is_const)
			Helper::array_set(self::instance()->constants, $path, $val);
		else
			Helper::array_set(self::instance()->variables, $path, $val);

		return true;
	}

	public static function get($path) {

		$path = strtolower(trim($path, ' .'));

		$variables = Helper::array_get(self::instance()->variables, $path, NULL);
		$constants = Helper::array_get(self::instance()->constants, $path, NULL);
		
		if (is_array($constants) && is_array($variables))
			return array_replace_recursive($variables, $constants);
		else if (!is_null($constants))
			return $constants;
		else if (!is_null($variables))
			return $variables;
		else
			return NULL;
	}
}

?>