<?php

class Rona {

	private static $instance;

	private
		$was_initialized = false,
		$autoloaders = [];
	
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	private static function instance() {

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}

	public static function init() {

		if (!self::instance()->was_initialized) {

			// Load class files
				require_once __DIR__ . '/Config.php';
				require_once __DIR__ . '/Helper.php';

			// Default configuration
				Config::set('rona')
					->_('working_root',	$_SERVER['DOCUMENT_ROOT'])
					->_('base_path',	'')
					->_('locations')
						->_('routes_api',	'/routes_api.php')
						->_('routes_app',	'/routes_app.php')
						->_('procedures',	'/model/procedures')
						->_('filters',		'/model/filters')
						->_('controllers',	'/app/controllers')
						->_('views',		'/app/views');

			// Register autoloader
				spl_autoload_register(function($class) {

					foreach (self::instance()->autoloaders as $autoloader)
						if (is_callable($autoloader) && $autoloader($class))
							return true;
				});

			// Load the developer's custom config file
				require_once Config::get('rona.working_root') . '/config.php';

			// Rona has been initialized
				self::instance()->was_initialized = true;
		}
	}

	public static function autoload_register($function) {
		self::instance()->autoloaders[] = $function;
	}
	
	public static function run() {

		// Initialize Rona
			self::init();

		// Load routes
			require_once __DIR__ . '/Route.php';
			require_once __DIR__ . '/Api.php';
			require_once Config::get('rona.working_root') . Config::get('rona.locations.routes_api');
			require_once Config::get('rona.working_root') . Config::get('rona.locations.routes_app');

		// Establish http method. If "_http_method" override was posted, use it. Otherwise, use default
			require_once __DIR__ . '/Request.php';
			Request::set('http_method', strtolower(!empty($_POST['_http_method']) ? $_POST['_http_method'] : $_SERVER['REQUEST_METHOD']));

		// Establish requested route
			$route_requested = str_replace(Config::get('rona.base_path'), '', $_SERVER['REQUEST_URI']);
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

		// Load classes
			require_once __DIR__ . '/Response.php';
			require_once __DIR__ . '/Procedure.php';

		// Run the procedure
			if (!empty($route_found['procedure'])) {

				$input = [];

				// If this is a "get" request, get the query string data
					if (Request::http_method() == 'get')
						parse_str($_SERVER['QUERY_STRING'], $input);

				// Since this isn't a "get" request, we'll get the input that was sent
					else {
						$content_type = Helper::array_get($_SERVER, 'CONTENT_TYPE');

						if ($content_type == 'application/x-www-form-urlencoded')
							parse_str(file_get_contents('php://input'), $input);

						# We're intentionally using the raw $_SERVER['REQUEST_METHOD'] here. This is a work around that will allow our manual put/patch/etc _http_method override methods to upload files
						elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && strstr($content_type, 'multipart/form-data') !== false) {
							$input = $_POST;
							$input = array_merge($input, $_FILES);
						}

						elseif ($content_type == 'application/json')
							$input = json_decode(file_get_contents('php://input'), true);

						else
							parse_str(file_get_contents('php://input'), $input);
					}

				// Get the route variables
					$input = array_merge($input, Request::route_vars());

				// Get the auth header
					if (!empty($_SERVER['HTTP_AUTH']))
						$input = array_merge($input, ['auth' => $_SERVER['HTTP_AUTH']]);
					
				// Run the procedure
					$procedure_res = Procedure::run($route_found['procedure'], $input);

				// If this is an api route, output in json format. Otherwise, the app will continue to load
					if (Helper::array_get($route_found, 'is_api')) {
						header('Content-Type: application/json');
						exit(json_encode($procedure_res));
					}
			}
			
		// Run the controllers
			require_once __DIR__ . '/Controller.php';
			require_once __DIR__ . '/Scope.php';
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
									Helper::load_file(Config::get('rona.working_root') . Config::get('rona.locations.views') . '/' . $view . '.php', false, false);
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
		Helper::load_file(Config::get('rona.working_root') . Config::get('rona.locations.' . $type . 's') . '/' . implode('/', $parts) . '.php');
		return $name;
	}
}

?>