<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2017 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
 */

namespace Rona;

use Exception;

class App {

	protected $config;

	protected $modules = [];

	public $http_request;

	public $route;

	public $scope;

	public $http_response;

	protected $methods_executed = [
		'start'					=> false,
		'find_route'			=> false,
		'execute_controllers'	=> false,
		'output'				=> false,
		'run'					=> false
	];

	public function __construct() {

		// Register the Rona classes using spl_autoload_register.
		$this->spl_autoload_register();

		// Create a config object for the app.
		$this->config = new \Rona\Config\Config;

		// Register the stock configuration.
		$this->register_stock_config();

		// Register the configuration.
		$this->register_config();

		// Create the HTTP Request, Route, Scope, and HTTP Response objects.
		$this->http_request = new \Rona\HTTP_Request($this);
		$this->route = new \Rona\Routing\Route($this);
		$this->scope = new \Rona\Scope;
		$this->http_response = new \Rona\HTTP_Response\Response($this);
	}

	protected function spl_autoload_register(): bool {
		return spl_autoload_register(function($class) {
			$file = dirname(__DIR__) . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
			if (file_exists($file)) {
				require_once $file;
				return true;
			}
			return false;
		});
	}

	private function register_stock_config() {
		$this->config()->set('base_path', '');
		$this->config()->set('request_path', strtok($_SERVER['REQUEST_URI'] ?? '', '?'));
		$this->config()->set('http_methods', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']);
		$this->config()->set('template_placeholder_replace_text', '%PH%');
		$this->config()->set('template_placeholder', '{% ' . $this->config('template_placeholder_replace_text') . ' %}');
		$this->config()->set('hook_prefix', 'hook_');
	}

	protected function register_config() {}

	public function config(string $item = NULL) {
		return is_null($item) ? $this->config : $this->config->get($item);
	}

	protected function register_modules() {}

	public function register_module(string $classname, string $name = NULL) {

		// Ensure the qualified class name is a subclass of the Rona module class.
		if (!is_subclass_of($classname, '\Rona\Module'))
			throw new Exception("$classname is not a subclass of '\Rona\Module'.");

		// Instantiate the module.
		$module = new $classname($this, $name);

		// Ensure the module name is unique.
		if ($this->module($module->name()))
			throw new Exception("Module names must be unique. {$module->name()} is already in use.");

		// Store the module in the app.
		$this->modules[$module->name()] = $module;

		// Execute the module's module_registered method.
		$module->module_registered();
	}

	public function modules(): array {
		return $this->modules;
	}

	public function module(string $name) {
		return $this->modules()[$name] ?? false;
	}

	public function run_hook(string $name, ...$args): array {

		// Create an empty array to hold the responses.
		$res = [];

		// Loop thru each module.
		foreach ($this->modules() as $module) {

			// If this module contains the hook, execute it and store the response.
			if (method_exists($module, $this->config('hook_prefix') . $name))
				$res[$module] = call_user_func_array([$module, $this->config('hook_prefix') . $name], $args);
		}

		// If the app contains the hook, execute it and store the response.
		if (method_exists($this, $this->config('hook_prefix') . $name))
			$res[$this] = call_user_func_array([$this, $this->config('hook_prefix') . $name], $args);

		// Response
		return $res;
	}

	public function start() {

		if (!$this->methods_executed['start']) {
			$this->methods_executed['start'] = true;

			// Register the modules.
			$this->register_modules();

			// Run a hook.
			$this->run_hook('modules_registered');			
		}
	}

	public function find_route() {

		if (!$this->methods_executed['find_route']) {
			$this->methods_executed['find_route'] = true;

			// Create a route matching object.
			$route_matcher = new \Rona\Routing\Matcher;
		
			// Loop thru each module and get the matching routes.
			$route_queues = [];
			$non_abstract = false;
			$non_abstract_module = NULL;
			$no_route = false;
			$no_route_module = NULL;
			foreach ($this->modules() as $module) {

				// Register this module's routes.
				$module->register_routes();

				foreach (['abstract', 'non_abstract', 'no_route'] as $type) {

					// Get the matching routes.
					$matches = $route_matcher->get_matches($module->route_store[$type]->get_routes(), $this->http_request->get_method(), $this->http_request->get_path());
					if (!empty($matches)) {
						if ($type == 'abstract') {
							foreach ($matches as $match)
								$route_queues[] = ['module' => $module, 'route_queue' => $match['route_queue']];
						} else {
							${$type} = array_pop($matches);
							${$type . '_module'} = $module;
						}
					}
				}
			}

			// Determine which route to use.
			if ($non_abstract || $no_route) {
				$this->route->route_found = true;
				if ($non_abstract) {
					$route_to_use = $non_abstract;
					$route_module_to_use = $non_abstract_module;
				} else {
					$this->route->is_no_route = true;
					$route_queues = [];
					$route_to_use = $no_route;
					$route_module_to_use = $no_route_module;
				}
			} else
				return false;			

			$this->http_request->set_path_vars($route_to_use['path_vars']);
			
			// Add the non-abstract route to the end of the route queues array.
			$route_queues[] = ['module' => $route_module_to_use, 'route_queue' => $route_to_use['route_queue']];

			// Loop thru each route queue and execute.
			foreach ($route_queues as $route_queue) {
				$this->route->set_active_module($route_queue['module']);
				$route_queue['route_queue']->process($this->route);
			}
		}
	}

	public function execute_controllers() {

		if (!$this->methods_executed['execute_controllers']) {
			$this->methods_executed['execute_controllers'] = true;

			// Run the controllers.
			while (1) {
				$controllers = $this->route->get_controllers();
				if (empty($controllers))
					break;
				$the_controller = $controllers[0];
				unset($controllers[0]);
				$this->route->remove_controllers();
				foreach ($controllers as $controller)
					$this->route->append_controller($controller);

				$this->route->set_active_module($the_controller['module']);
				$this->http_response->set_active_module($the_controller['module']);

				$res = call_user_func($the_controller['callback'], $this->http_request, $this->route, $this->scope, $this->http_response);
				if ($res === false)
					break;
				else if (is_object($res) && is_a($res, '\Rona\HTTP_Response\Response'))
					$this->http_response = $res;
			}
		}
	}

	public function output() {

		if (!$this->methods_executed['output']) {
			$this->methods_executed['output'] = true;

			$this->http_response->output();

			// Run a hook.
			$this->run_hook('http_response_sent');
		}	
	}

	public function run() {

		if (!$this->methods_executed['run']) {
			$this->methods_executed['run'] = true;

			$this->start();

			$this->find_route();

			$this->execute_controllers();

			$this->output();
		}
	}

	public function hook_view_template($module, $template) {
		return $template;
	}

	public function hook_view_stylesheet($module, $item) {
		return "<link href=\"$item\" rel=\"stylesheet\">
		";
	}

	public function hook_view_javascript($module, $item) {
		return "<script src=\"$item\"></script>
		";
	}

	public function hook_view_file($module, $item) {
		return $item;
	}
}