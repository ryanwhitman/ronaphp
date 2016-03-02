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
					->_('debug_mode', true)
					->_('base_path', '')
					->_('base_dir', dirname(__DIR__))
					->_('core_dir', __DIR__)
					->_('tmp_storage', '/cgi-bin/tmp')
					->_('header_input', [])
					->_('http_methods', ['get', 'post', 'put', 'patch', 'delete', 'options'])
					->_('locations')
						->_('config_model', '/model/config.php')
						->_('api', '/model/api.php')
						->_('filters', '/model/filters')
						->_('procedures', '/model/procedures')
						->_('config_app', '/app/config.php')
						->_('routes', '/app/routes.php')
						->_('controllers', '/app/controllers')
						->_('views', '/app/views');

			// Load the config files
				require_once Config::get('rona.base_dir') . '/config.php';
				require_once Config::get('rona.base_dir') . Config::get('rona.locations.config_model');
				require_once Config::get('rona.base_dir') . Config::get('rona.locations.config_app');

			// Error handling
				if (Config::get('rona.debug_mode')) {
					ini_set('display_errors', 1);
					ini_set('display_startup_errors', 1);
					error_reporting(-1);
				} else {
					ini_set('display_errors', 0);
					ini_set('display_startup_errors', 0);
					error_reporting(0);
				}

			// Register autoloader
				spl_autoload_register(function($class) {

					foreach (self::instance()->autoloaders as $autoloader)
						if (is_callable($autoloader) && $autoloader($class))
							return true;
				});

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
			require_once Config::get('rona.core_dir') . '/Route.php';
			require_once Config::get('rona.core_dir') . '/Api.php';
			require_once Config::get('rona.core_dir') . '/App.php';
			require_once Config::get('rona.base_dir') . Config::get('rona.locations.api');
			require_once Config::get('rona.base_dir') . Config::get('rona.locations.routes');

		// Establish http method. If "_http_method" override was posted, use it. Otherwise, use default
			require_once Config::get('rona.core_dir') . '/Request.php';
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
			$direct_match = Helper::array_get(Route::get_routes(), Request::http_method() . '.regular.' . Request::route(), NULL);
			if (!is_null($direct_match))
				$route_found = $direct_match;
			else {

				$variable_matches = Helper::array_get(Route::get_routes(), Request::http_method() . '.variable', []);
				foreach ($variable_matches as $path => $components) {
					
					// Reset route_var array
						$route_vars = [];
					
					// Explode the route being examined into an array
						$route_examining_arr = explode('/', $path);
					
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
								$route_found = $components;
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
					$route_found['views'] = ['"Uh oh, that page wasn\'t found."'];
			}

		// Is this an API call?
			$is_api = Helper::array_get($route_found, 'is_api');
			
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
			require_once Config::get('rona.core_dir') . '/Response.php';
			require_once Config::get('rona.core_dir') . '/Procedure.php';

		// Start session
			if (session_status() == PHP_SESSION_NONE && !$is_api) {
				$save_path = Config::get('rona.base_dir') . Config::get('rona.tmp_storage');
				if (!file_exists($save_path))
					mkdir($save_path, 0777, true);
				session_save_path($save_path);
				session_start();
			}

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

				// Get the header input
					$header_input = (array) Config::get('rona.header_input');
					foreach ($header_input as $item) {
						$val = $is_api ? Helper::array_get($_SERVER, strtoupper('http_' . $item)) : Helper::array_get($_SESSION, $item);
						$input = array_merge($input, [$item => $val]);
					}
					
				// Run the procedure
					$procedure_res = Procedure::run($route_found['procedure'], $input, Helper::array_get($route_found, 'filters', []));

				// If this is an api route, output in json format. Otherwise, the app will continue to load
					if ($is_api) {
						header('Content-Type: application/json');
						exit(json_encode($procedure_res));
					}
			}

		// Create the scope object
			require_once Config::get('rona.core_dir') . '/Scope.php';
			$scope = Scope::instance();
			if (isset($procedure_res))
				$scope->procedure_res = $procedure_res;
			
		// Run the controllers
			require_once Config::get('rona.core_dir') . '/Controller.php';
			if (!empty($route_found['controllers']) && is_array($route_found['controllers'])) {
				foreach ($route_found['controllers'] as $controller) {
					
					if (is_callable($controller))
						$controller = $controller($scope);
					
					if (!empty($controller))
						Controller::run($controller, $scope);
				}
			}
			
		// Run the views
			if (!empty($route_found['views']) && is_array($route_found['views'])) {
				
				$output = '';
				foreach ($route_found['views'] as $view) {
					
					if (is_callable($view))
						$view = $view($scope);
					
					if (!empty($view)) {
						ob_start();

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
									self::load_view($view, $scope);
							$contents = ob_get_contents();
						ob_end_clean();
					}

					if (empty($output))
						$output = $contents;
					else {
					
						// Escape $n backreferences
							$contents = preg_replace('/\$(\d)/', '\\\$$1', $contents);
							
						$output = preg_replace('/{rona_replace}/', $contents, $output, 1);
					}
				}
				
				// Remove any remaining rona_replace place holders and output the views
					echo str_replace('{rona_replace}', '', $output);
			}
	}

	public static function tLoad($type, $name) {

		$parts = explode('.', $name);
		$name = end($parts);
		unset($parts[count($parts) - 1]);
		Helper::load_file(Config::get('rona.base_dir') . Config::get('rona.locations.' . $type . 's') . '/' . implode('/', $parts) . '.php');
		return $name;
	}

	public static function load_view($view, $scope) {
		include Config::get('rona.base_dir') . Config::get('rona.locations.views') . '/' . $view . '.php';
	}
}

?>