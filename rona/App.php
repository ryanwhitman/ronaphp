<?php

class App {

	private static $instance;
	
	private function __construct() {}

	private function __clone() {}

	private function __wakeup() {}

	private static function instance() {

		if (self::$instance == NULL) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function init() {

		// Instantiate
			self::instance();

		// Register autoloader
			spl_autoload_register(function($class) {
				require_once(__DIR__ . '/' . $class . '.php');
			});
	}
	
	public static function run($options = []) {

		// Load routes
			self::load_directories(['routes' => []]);
		
		// Reset Request
			Request::reset();

		// Establish defaults && route
			if (isset($options['route_requested'])) {
				$requested_route = trim($options['route_requested'], '/ ');
			} else {
				$options['base_path'] = Helper::get($options['base_path']);
				$requested_route = str_replace($options['base_path'], '', $_SERVER['REQUEST_URI']);
				$requested_route = strtok($requested_route, '?');
				$requested_route = trim($requested_route, ' /');
			}
			Request::set('route', $requested_route);

		// Turn the requested route into an array & get the count
			$route_requested_arr = explode('/', Request::route());
			$route_requested_count = count($route_requested_arr);
			
		// Establish an empty $route_found variable
			$route_found = '';
			
		// First attempt to find a direct match. If that fails, try matching a path with a route variable in it.
			if (isset(Route::get_routes()[Request::http_method()]['regular'][Request::route()])) {
				$route_found = Route::get_routes()[Request::http_method()]['regular'][Request::route()];
			} elseif (!empty(Route::get_routes()[Request::http_method()]['variable']) && is_array(Route::get_routes()[Request::http_method()]['variable'])) {
				
				foreach (Route::get_routes()[Request::http_method()]['variable'] as $k => $v) {
					
					// Reset route_var array
						$route_vars = [];
					
					// Explode the route being examined into an array
						$route_examining_arr = explode('/', $k);
					
					// Ensure the arrays are the same size
						if ($route_requested_count == count($route_examining_arr)) {
						
							// Iterate thru each of the array elements. The requested route and the route being examined either need to match exactly or the route being examined needs to have a variable.
								$matches_needed = $route_requested_count;
								$matches_found = 0;
								for ($i = 0; $i < $matches_needed; $i++) {
									
									if ($route_requested_arr[$i] == $route_examining_arr[$i]) {
									
										// An exact match was found, so we'll continue to the next array item.
											$matches_found++;
											
									} else if (preg_match('/^{.+}$/', $route_examining_arr[$i])) {
									
										// The route being examined has a route variable, so it's a match. Set route_var array for use later on.
											$route_vars[str_replace(array('{', '}'), '', $route_examining_arr[$i])] = $route_requested_arr[$i];
											$matches_found++;
											
									} else {
									
										// A match was not found, so the route being examined isn't a match.
											break;
									}
								}
								
							if ($matches_found == $matches_needed) {
								$route_found = $v;
								break;
							}
						}
				}
			}
		
		// If $route_found is empty, load no_route
			if (empty($route_found)) {
				header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
				$route_found = Route::get_no_route();
			}
			
		// Find and apply wildcards
			elseif (!empty(Route::get_routes()[Request::http_method()]['wildcard']) && is_array(Route::get_routes()[Request::http_method()]['wildcard'])) {
							
				$wildcard_arr = [];
				
				foreach (Route::get_routes()[Request::http_method()]['wildcard'] as $k => $v) {
					
					$route_examining_arr = explode('/', $k);
					
					$is_match = false;
					for ($i = 0; $i < $route_requested_count; $i++) {
						
						if ($route_examining_arr[$i] == $route_requested_arr[$i] || $route_examining_arr[$i] == '*') {
						
							// Get the count, which is the current iteration (array index) plus 1
								$count = $i + 1;
							
							if ($count == count($route_examining_arr) && ($count == $route_requested_count || $route_examining_arr[$i] == '*')) {
								
								$is_match = true;
								break;
							}
						
						} else {
							break;
						}
					}
					
					if ($is_match) {
						$wildcard_arr = array_merge_recursive($wildcard_arr, $v);
					}
				}
			}
			
		// Add the wildcard array
			if (!empty($wildcard_arr)) {
				$route_found = array_merge_recursive($wildcard_arr, $route_found);
			}
						
		// Set the current route_vars
			if (!empty($route_vars)) {
				Request::set('route_vars', $route_vars);
			}
			
		// Set the current route_ids
			if (!empty($route_found['ids'])) {
				Request::set('route_ids', $route_found['ids']);
			}
			
		// Set the current route_tags
			if (!empty($route_found['tags'])) {
				Request::set('route_tags', $route_found['tags']);
			}
			
		// Set the current route_options
			if (!empty($route_found['options'])) {
				Request::set('route_options', $route_found['options']);
			}

		// Run the procedure
			if (!empty($route_found['procedure'])) {
				
				// Get input values
					if (Request::http_method() == 'get') {
						$input = $_GET;
					} else {
						parse_str(file_get_contents('php://input'), $input);
					}
					$input = array_merge($input, Request::route_vars());
					
				// Run the procedure
					$procedure_ret = Procedure::run($route_found['procedure'], $input);
			}
			
		// Run the controllers
			if (!empty($route_found['controllers']) && is_array($route_found['controllers'])) {
				foreach ($route_found['controllers'] as $controller) {
					
					$procedure_ret = Helper::get($procedure_ret);

					if (is_callable($controller)) {
						$controller = $controller($procedure_ret);
					}
					
					if (!empty($controller)) {
						Controller::run($controller, $procedure_ret);
					}
				}
			}
			
		// Run the views
			if (!empty($route_found['views']) && is_array($route_found['views'])) {
				
				$output = '';
				foreach ($route_found['views'] as $view) {
					
					if (is_callable($view)) {
						$view = $view();
					}
					
					ob_start();
						if (!empty($view)) {

							// If the view is wrapped in quotes, simply output the string
								$first_char = substr($view, 0, 1);
								$last_char = substr($view, -1, 1);
								if (($first_char == '"' && $last_char == '"') || ($first_char == "'" && $last_char == "'")) {
									$view = substr($view, 1);
									$view = substr($view, 0, -1);
									echo $view;
								}

							// The view was not a string output, so include the file
								else {
									self::load_file($_SERVER['DOCUMENT_ROOT'] . '/views/' . $view . '.php', false, false);
								}
						}
						$contents = ob_get_contents();
					ob_end_clean();
				
					if (empty($output)) {
						$output = $contents;
					} else {
					
						// Escape $n backreferences
							$contents = preg_replace('/\$(\d)/', '\\\$$1', $contents);
							
						$output = preg_replace('/{rona_replace}/', $contents, $output, 1);
					}
				}
				
				// Remove any remaining rona_replace place holders
					$output = str_replace('{rona_replace}', '', $output);
			
				echo $output;
				return;
			}
			
		// Output the procedure
			if (!empty($procedure_ret))
				echo json_encode($procedure_ret);
	}

	public static function load_group($full_name) {

		$parts = explode('.', $full_name);
		$name = end($parts);
		unset($parts[count($parts) - 1]);
		self::load_file($_SERVER['DOCUMENT_ROOT'] . '/' . implode('/', $parts) . '.php');
		return $name;
	}

	public static function load_directories($directories = array(), $require = true, $once = true) {

		foreach ($directories as $directory => $options) {
						
			if (isset($options['precedence'])) {
				$options['precedence'] = (array) $options['precedence'];
				foreach ($options['precedence'] as $precedence) {
					self::load_file($_SERVER['DOCUMENT_ROOT'] . '/' . $directory . '/' . $precedence . '.php', $require, $once);
				}
			}
			
			foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/' . $directory . '/*.php') as $filename) {
				self::load_file($filename, $require, $once);
			}
		}
	}
				
	public static function load_file($file, $require = true, $once = true) {
		if ($require)
			if ($once)
				require_once($file);
			else
				require($file);
		else
			if ($once)
				include_once($file);
			else
				include($file);
	}
	
	public static function ret($success, $message = '', $data = []) {
		return array(
			'success'	=>	(bool) $success,
			'message'	=>	$message,
			'data'		=>	(array) $data
		);
	}

	public static function location($base, $query_params = []) {

		$query_string = '';
		if (!empty($query_params) && is_array($query_params)) {
			$query_string = '?';
			foreach ($query_params as $k => $v) {
				$query_string .= $k . '=' . $v . '&';
			}
			$query_string = trim($query_string, '&');
		}

		header('Location: ' . $base . $query_string);
		exit;
	}
}

?>