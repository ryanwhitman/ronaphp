<?php

class Rona {

	private static $instance;

	private $settings;
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	private static function instance() {

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}
	
	public static function run($settings = []) {

		// Instantiate
			self::instance();

		// Establish settings
			$default_settings = [
				'working_root'	=> $_SERVER['DOCUMENT_ROOT'],
				'locations'		=> [
					'routes_api'	=> '/api/routes.php',
					'procedures'	=> '/api/procedures',
					'profilters'	=> '/api/profilters',
					'routes_app'	=> '/app/routes.php',
					'controllers'	=> '/app/controllers',
					'views'			=> '/app/views'
				],
				'base_path'		=> ''
			];
			self::instance()->settings = array_replace_recursive($default_settings, $settings);

		// Register autoloader
			spl_autoload_register(function($class) {
				require_once(__DIR__ . '/' . $class . '.php');
			});

		// Load routes
			require_once self::get_setting('working_root') . self::get_setting('location.routes_api');
			require_once self::get_setting('working_root') . self::get_setting('location.routes_app');

		// Establish http method. If "_http_method" override was posted, use it. Otherwise, use default
			Request::set('http_method', strtolower(!empty($_POST['_http_method']) ? $_POST['_http_method'] : $_SERVER['REQUEST_METHOD']));

		// Establish requested route
			$route_requested = str_replace(self::get_setting('base_path'), '', $_SERVER['REQUEST_URI']);
			$route_requested = strtok($route_requested, '?');
			$route_requested = trim($route_requested, ' /');
			Request::set('route', $route_requested);

		// Turn the requested route into an array & get the count
			$route_requested_arr = explode('/', Request::route());
			$route_requested_count = count($route_requested_arr);
			
		// Establish an empty $route_found variable
			$route_found = '';
			
		// First attempt to find a direct match. If that fails, try matching a route with a variable in it.
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
				if (empty($route_found))
					$route_found['views'] = ['"<span style="position: absolute; top: 30%; right: 20px; left: 20px; text-align: center; font-weight: bold; font-size: 25px;">Welcome to Rona! Looks like you need to create some routes!</span>"'];
			}
			
		// Set the current route_vars
			if (!empty($route_vars))
				Request::set('route_vars', $route_vars);
			
		// Set the current route_tags
			if (!empty($route_found['tags']))
				Request::set('route_tags', $route_found['tags']);
			
		// Set the current route_options
			if (!empty($route_found['options']))
				Request::set('route_options', $route_found['options']);

		// Run the procedure
			if (!empty($route_found['procedure'])) {
				
				// Get input values
					if (Request::http_method() == 'get')
						$input = $_GET;
					else
						parse_str(file_get_contents('php://input'), $input);

					$input = array_merge($input, Request::route_vars());
					
				// Run the procedure
					$procedure_res = Procedure::run($route_found['procedure'], $input);

				// If this is an api route, output in json format. Otherwise, the app will continue to load
					if (Helper::array_get($route_found, 'is_api'))
						exit(json_encode($procedure_res));
			}
			
		// Run the controllers
			if (!empty($route_found['controllers']) && is_array($route_found['controllers'])) {
				foreach ($route_found['controllers'] as $controller) {
					
					$procedure_res = Helper::get($procedure_res);

					if (is_callable($controller)) {
						$controller = $controller($procedure_res);
					}
					
					if (!empty($controller)) {
						Controller::run($controller, $procedure_res);
					}
				}
			}
			
		// Run the views
			if (!empty($route_found['views']) && is_array($route_found['views'])) {
				
				$output = '';
				foreach ($route_found['views'] as $view) {
					
					if (is_callable($view))
						$view = $view();
					
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
								else
									Helper::load_file(self::get_setting('working_root') . self::get_setting('location.views') . '/' . $view . '.php', false, false);
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
				
				// Remove any remaining rona_replace place holders and output the views
					$output = str_replace('{rona_replace}', '', $output);
					echo $output;
					return;
			}
	}

	public static function tLoad($type, $name) {

		$parts = explode('.', $name);
		$name = end($parts);
		unset($parts[count($parts) - 1]);
		Helper::load_file(self::get_setting('working_root') . self::get_setting('location.' . $type . 's') . '/' . implode('/', $parts) . '.php');
		return $name;
	}

	public static function get_setting($setting_name) {

		$parts = explode('.', $setting_name);
		if ($parts[0] == 'location') $parts[0] = 'locations';
		$setting_name = implode('.', $parts);

		return Helper::array_get(self::instance()->settings, $setting_name);
	}
}

?>