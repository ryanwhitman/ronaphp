<?php
/**
 * This file houses the App class. Additionally, it loads the App class dependencies.
 *
 * @package RonaPHP
 * @copyright Copyright (c) 2016 Ryan Whitman (http://www.ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT MIT
 * @version .5.4.1
 * @link https://github.com/RyanWhitman/ronaphp
 * @since .5.4.1
 */

require_once Config::get('rona.core_dir') . '/Route.php';

class App {

	private static $instance;

	private $no_route = [];
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public static function instance() {

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}

	public static function get($path, $components) {
		self::map('get', $path, $components);
	}

	public static function post($path, $components) {
		self::map('post', $path, $components);
	}

	public static function put($path, $components) {
		self::map('put', $path, $components);
	}

	public static function patch($path, $components) {
		self::map('patch', $path, $components);
	}

	public static function delete($path, $components) {
		self::map('delete', $path, $components);
	}

	public static function options($path, $components) {
		self::map('options', $path, $components);
	}

	public static function any($path, $components) {
		self::map(Config::get('rona.http_methods'), $path, $components);
	}
	
	public static function map($http_methods, $path, $components) {
		Route::map($http_methods, $path, $components);
	}
	
	public static function no_route($components) {

		// If singular nouns were used, transfer them to plural
			if (!empty($components['controller']))
				$components['controllers'] = $components['controller'];
			
			if (!empty($components['view']))
				$components['views'] = $components['view'];
			
			if (!empty($components['option']))
				$components['options'] = $components['option'];

		// Establish $components_formatted array
			$components_formatted = [];

			if (!empty($components['controllers']))
				$components_formatted['controllers'] = (array) $components['controllers'];

			if (!empty($components['views']))
				$components_formatted['views'] = (array) $components['views'];

			if (!empty($components['options']))
				$components_formatted['options'] = (array) $components['options'];

		// A valid no_route must contain a controller or view
			if (empty($components_formatted['controllers']) && empty($components_formatted['views']))
				return false;
	
		// Add no_route
			self::instance()->no_route = $components_formatted;
	}

	public static function get_no_route() {
		return self::instance()->no_route;
	}
	
}