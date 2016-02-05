<?php

class Route {

	private static $instance;

	public static $http_methods = ['get', 'post', 'put', 'patch', 'delete', 'options'];

	private
		$routes = [],
		$no_route = [];
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	private static function instance() {

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
		self::map(self::$http_methods, $path, $components);
	}
	
	public static function map($http_methods, $path, $components) {

		// If singular nouns were used, transfer them to plural
			if (!empty($components['controller']))
				$components['controllers'] = $components['controller'];
			
			if (!empty($components['view']))
				$components['views'] = $components['view'];
			
			if (!empty($components['option']))
				$components['options'] = $components['option'];
			
			if (!empty($components['tag']))
				$components['tags'] = $components['tag'];

		// Establish $components_formatted array
			$components_formatted = [];

			$is_api = $components_formatted['is_api'] = Helper::array_get($components, 'is_api') === true ? true : false;

			if (!empty($components['procedure']))
				$components_formatted['procedure'] = (string) $components['procedure'];

			if (!$is_api) {

				if (!empty($components['controllers']))
					$components_formatted['controllers'] = (array) $components['controllers'];

				if (!empty($components['views']))
					$components_formatted['views'] = (array) $components['views'];

				if (!empty($components['options']))
					$components_formatted['options'] = (array) $components['options'];

				if (!empty($components['tags']))
					$components_formatted['tags'] = (array) $components['tags'];
			}

		// A valid route must contain a procedure, controller, or view
			if (empty($components_formatted['procedure']) && empty($components_formatted['controllers']) && empty($components_formatted['views']))
				return false;
			
		// Ensure path is in correct format
			$path = (string) trim(strtolower($path), '/ ');
			
		// Determine route type
			if (!$is_api && preg_match('/[*]/i', $path))
				$type = 'wildcard';
			elseif (preg_match('/\/{.+(}$|}\/)/', $path))
				$type = 'variable';
			else
				$type = 'regular';

		// Turn the path into an array & get the count
			$path_arr = explode('/', $path);
			$path_count = count($path_arr);
			
		// Add routes for each http method
			$http_methods = (array) $http_methods;
			foreach ($http_methods as $http_method) {
			
				// Find and attach wildcards
					if ($is_api &&
						$type != 'wildcard' &&
						!empty(self::get_routes()[$http_method]['wildcard']) &&
						is_array(self::get_routes()[$http_method]['wildcard'])
					) {
						
						$wildcard_arr = [];
						
						foreach (self::get_routes()[$http_method]['wildcard'] as $k => $v) {
							
							$path_examining_arr = explode('/', $k);
							
							$is_match = false;
							for ($i = 0; $i < $path_count; $i++) {
								
								if ($path_examining_arr[$i] == $path_arr[$i] || $path_examining_arr[$i] == '*') {
								
									// Get the count, which is the current iteration (array index) plus 1
										$count = $i + 1;
									
									if ($count == count($path_examining_arr) && ($count == $path_count || $path_examining_arr[$i] == '*')) {
										
										$is_match = true;
										break;
									}
								
								} else
									break;
							}
							
							if ($is_match)
								$wildcard_arr = array_merge_recursive($wildcard_arr, $v);
						}
					
						// Add the wildcard array
							if (!empty($wildcard_arr))
								$components_formatted = array_merge_recursive($wildcard_arr, $components_formatted);
					}

				self::instance()->routes[$http_method][$type][$path] = array_merge_recursive(Helper::get(self::instance()->routes[$http_method][$type][$path], []), $components_formatted);
			}
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

	public static function get_routes() {
		return self::instance()->routes;
	}

	public static function get_no_route() {
		return self::instance()->no_route;
	}
	
	public static function is_active($path) {
		return Request::route() == trim($path, ' /');
	}
	
	public static function tag_is_active($tag) {
		return in_array($tag, Request::route_tags());
	}
}

?>