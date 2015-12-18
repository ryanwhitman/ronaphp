<?php

class Route {

	private static $instance;

	public static $http_methods = ['get', 'post', 'put', 'patch', 'delete', 'head', 'options'];

	private
		$routes = [],
		$routes_by_id = [],
		$no_route = [];
	
	private function __construct() {}

	private function __clone() {}

	private function __wakeup() {}

	private static function instance() {

		if (self::$instance == NULL) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function get($ids, $path, $components) {
		self::custom('get', $ids, $path, $components);
	}

	public static function post($ids, $path, $components) {
		self::custom('post', $ids, $path, $components);
	}

	public static function put($ids, $path, $components) {
		self::custom('put', $ids, $path, $components);
	}

	public static function patch($ids, $path, $components) {
		self::custom('patch', $ids, $path, $components);
	}

	public static function delete($ids, $path, $components) {
		self::custom('delete', $ids, $path, $components);
	}

	public static function head($ids, $path, $components) {
		self::custom('head', $ids, $path, $components);
	}

	public static function options($ids, $path, $components) {
		self::custom('options', $ids, $path, $components);
	}

	public static function any($ids, $path, $components) {
		self::custom(self::$http_methods, $ids, $path, $components);
	}
	
	public static function custom($http_methods, $ids, $path, $components) {

		// Ensure $http_methods is an array
			$http_methods = (array) $http_methods;
		
		// Establish $components_formatted array
			$components_formatted = [];
			if (!empty($ids)) {
				$components_formatted['ids'] = (array) $ids;
			}

			if (isset($components['tags'])) {
				$components_formatted['tags'] = (array) $components['tags'];
			}

			if (isset($components['procedure'])) {
				$components_formatted['procedure'] = (string) $components['procedure'];
			}

			if (isset($components['controllers'])) {
				$components_formatted['controllers'] = (array) $components['controllers'];
			}

			if (isset($components['views'])) {
				$components_formatted['views'] = (array) $components['views'];
			}

			if (isset($components['options'])) {
				$components_formatted['options'] = (array) $components['options'];
			}
			
		// Ensure path is in correct format
			$path = (string) trim(strtolower($path), '/ ');
			
		// Determine route type
			if (preg_match('/[*]/i', $path)) {
				$type = 'wildcard';
			} elseif (preg_match('/\/{.+(}$|}\/)/', $path)) {
				$type = 'variable';
			} else {
				$type = 'regular';
			}
			
		// Add routes for each http method
			foreach ($http_methods as $http_method) {
				self::instance()->routes[$http_method][$type][$path] = array_merge_recursive(Helper::get(self::instance()->routes[$http_method][$type][$path], []), $components_formatted);
			}
			
		// Add route by id
			if (!empty($components_formatted['ids'])) {
				foreach ($components_formatted['ids'] as $id) {

					// Ensure id is unique
						if (isset(self::instance()->routes_by_id[$id]))
							throw new Exception("'$id' is not a unique id.");

					self::instance()->routes_by_id[$id] = [
						'type'	=>	$type,
						'path'	=>	$path
					];
				}
			}
	}
	
	public static function no_route($components) {
		
		// Establish $components_formatted array
			$components_formatted = [];
			if (isset($components['controllers'])) {
				$components_formatted['controllers'] = (array) $components['controllers'];
			}

			if (isset($components['views'])) {
				$components_formatted['views'] = (array) $components['views'];
			}

			if (isset($components['options'])) {
				$components_formatted['options'] = (array) $components['options'];
			}
	
		// Add no_route
			self::instance()->no_route = $components_formatted;
	}

	public static function get_routes() {
		return self::instance()->routes;
	}

	public static function get_routes_by_id() {
		return self::instance()->routes_by_id;
	}

	public static function get_no_route() {
		return self::instance()->no_route;
	}
	
	public static function path($id, $vars = []) {

		// Get the route and ensure one was found
			$routes = self::get_routes_by_id();
			if (empty($routes[$id])) return '';
			$route = $routes[$id];

		if ($route['type'] == 'variable') {
			$route_arr = explode('/', $route['path']);
			$var_i = 0;
			for ($i = 0; $i < count($route_arr); $i++) {
				if (preg_match('/^{.+}$/', $route_arr[$i])) {
					$return_route[$i] = $vars[$var_i];
					$var_i++;
				} else {
					$return_route[$i] = $route_arr[$i];
				}
			}
			$return_route = implode('/', $return_route);
		} else {
			$return_route = $route['path'];
		}
		
		return '/' . trim($return_route, '/ ');
	}
	
	public static function change($id, $vars = [], $query_params = []) {
		App::location(self::path($id, $vars), $query_params);
	}
	
	public static function is_active($id) {
		return in_array($id, Request::route_ids());
	}
	
	public static function tag_is_active($tag) {
		return in_array($tag, Request::route_tags());
	}
}

?>