<?php

class Route {

	private static $instance;

	public $routes = [];
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public static function instance() {

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}
	
	public static function map($http_methods, $path, $components) {

		// Does this route belong to the API or app?
		$is_api = Helper::array_get($components, 'is_api') === true ? true : false;

		// Ensure path is in correct format
		//$path = (string) trim(strtolower($path), '/');
		$path = (string) strtolower($path);
		if ($path == '/')
			$path = '';
			
		// Determine route type
		if (!$is_api && preg_match('/[*]/i', $path))
			$type = 'wildcard';
		else if (preg_match('/\/{.+(}$|}\/)/', $path))
			$type = 'variable';
		else
			$type = 'regular';

		// Establish $components_formatted array
		$components_formatted = [];

		if ($is_api) {

			$components_formatted['procedure'] = (string) $components['procedure'];

			// Set the default hooks
			$default_hooks = Config::get('rona.api.hooks');
			$components_formatted['hooks'] = [
				'onAuthentication_failure'	=> $default_hooks['onauthentication_failure'],
				'onParam_failure'			=> $default_hooks['onparam_failure'],
				'onAuthorization_failure'	=> $default_hooks['onauthorization_failure'],
				'onSuccess'					=> $default_hooks['onsuccess']
			];

		} else {

			if (!empty($components['controllers']))
				$components_formatted['controllers'] = (array) $components['controllers'];			

			if (!empty($components['views']))
				$components_formatted['views'] = (array) $components['views'];			

			if (!empty($components['options']))
				$components_formatted['options'] = (array) $components['options'];			

			if (!empty($components['tags']))
				$components_formatted['tags'] = (array) $components['tags'];

			// Only proceed if a component exists
			if (empty($components_formatted))
				return false;
		}

		// Turn the path into an array & get the count
		$path_arr = explode('/', $path);
		$path_count = count($path_arr);
			
		// Add routes for each http method
		foreach ((array) $http_methods as $http_method) {

			// Merge these components with those previously created for this route. This only applies to App routes.
			if (!$is_api)
				$components_formatted = array_merge_recursive(Helper::array_get(self::get_routes(), $http_method . '.' . $type . '.' . $path, []), $components_formatted);
		
			// Find and attach wildcard components
			if (!$is_api && $type != 'wildcard') {

				$wc_routes = Helper::array_get(self::get_routes(), $http_method . '.wildcard', []);
				$wc_components_all = [];
				
				foreach ($wc_routes as $wc_path => $wc_components) {
					
					$path_examining_arr = explode('/', $wc_path);
					
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
						$wc_components_all = array_merge_recursive($wc_components_all, $wc_components);
				}
			
				// Add the wildcard array
				$components_formatted = array_merge_recursive($wc_components_all, $components_formatted);
			}

			// Add the "is_api" attribute to the route components array. This doesn't apply to wildcards
			if ($type != 'wildcard')
				$components_formatted['is_api'] = $is_api;

			// Set the route
			self::instance()->routes[$http_method][$type][$path] = $components_formatted;

			// Return the type so it can be used in the Api class
			return $type;
		}
	}

	public static function get_routes() {
		return self::instance()->routes;
	}
	
	public static function is_active($path) {
		//return Request::route() == trim($path, '/');
		return Request::route() == $path;
	}
	
	public static function tag_is_active($tag) {
		return in_array($tag, Request::route_tags());
	}
}

?>