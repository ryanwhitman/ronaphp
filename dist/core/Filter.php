<?php
/**
 * This file houses the Filter class.
 *
 * @package RonaPHP
 * @copyright Copyright (c) 2016 Ryan Whitman (http://www.ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT MIT
 * @version .5.4.1
 * @link https://github.com/RyanWhitman/ronaphp
 * @since .5.4.1
 */

class Filter {

	private static $instance;
	
	private $filters = [];
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public static function instance() {

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}
	
	public static function set($name, $default_options, $function) {
		self::instance()->filters[Rona::get_tLoad_namespace() . '.' . $name] = [
			'default_options'	=> (array) $default_options,
			'function'			=> $function
		];
	}
	
	public static function run($fullname, $val, $label, $options = []) {

		// Targeted load
		Rona::tLoad('filter', $fullname);

		// Get the filter
		$filter = Helper::array_get(self::instance()->filters, [$fullname]);

		// Ensure filter exists
		if (empty($filter))
			throw new Exception('The filter "' . $fullname . '" does not exist.');

		// Merge the option arrays
		$options = array_merge($filter['default_options'], $options);

		// Run the filter
		$res = $filter['function']($val, $label, $options);

		// If the filter failed and there is no message, attach a default one
		if (!$res->success && empty($res->messages))
			$res->messages[] = Helper::func_or(Config::get('rona.filters.messages.default.failure'), get_defined_vars());

		// Return the response object
		return $res;
	}
}