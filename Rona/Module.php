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
use Rona\App;
use Rona\Routing\Store;
use Rona\Config\Config;
use Rona\Response;

class Module {

	protected $name;

	protected $app;

	protected $config;

	/**
	 * An array that holds the services.
	 * 
	 * @var array
	 */
	protected $services = [];

	/**
	 * An associative array that holds the return value of service callbacks. Key = service name; Value = the return value of the service callback.
	 * 
	 * @var array
	 */
	protected $service_callback_ret_vals = [];

	public function __construct(App $app, string $name = NULL) {

		// If a module name was passed in thru the construct, set it.
		if (!is_null($name))
			$this->name = $name;

		// Prepare the module name and ensure it exists.
		$this->name = strtolower(trim($this->name()));
		if (!$this->name())
			throw new Exception('A module must have a name.');

		// Set the Rona instance.
		$this->app = $app;

		// Create a config object for the module.
		$this->config = new Config;

		// Register the module's config.
		$this->register_config();

		// Register the module's services.
		$this->register_services();

		// Create route store objects for the module.
		$this->route_store = [
			'abstract'			=> new Store($this->app()->config('http_methods')),
			'non_abstract'		=> new Store($this->app()->config('http_methods')),
			'no_route'			=> new Store($this->app()->config('http_methods'))
		];
	}

	public function name() {
		return $this->name;
	}

	public function app(): App {
		return $this->app;
	}

	public function config(string $item = NULL) {
		return is_null($item) ? $this->config : $this->config->get($item);
	}

	protected function register_config() {}

	public function module_registered() {}

	/**
	 * A holding method to register services.
	 * 
	 * @return void
	 */
	protected function register_services() {}

	/**
	 * Register a service.
	 * 
	 * @param     string      $name        The name of the service.
	 * @param     callable    $callback    The callback to execute.
	 * @return    void
	 */
	public function register_service(string $name, callable $callback) {

		// Ensure service name hasn't already been registered.
		if (isset($this->services[$name]))
			throw new Exception("The service '$name' has already been registered.");

		// Set the service.
		$this->services[$name] = $callback;
	}

	/**
	 * Get a service.
	 * 
	 * @param     string      $name       The name of the service.
	 * @param     mixed       $args       Args that get passed to the service callback.
	 * @return    mixed                   The value returned from the service callback.
	 */
	public function get_service(string $name, ...$args) {

		// Ensure the service has been registered.
		if (!isset($this->services[$name]))
			throw new Exception("The service '$name' has not been registered.");

		// If the service callback has not already been executed, execute it.
		if (!isset($this->service_callback_ret_vals[$name])) {
			array_unshift($args, $this);
			$this->service_callback_ret_vals[$name] = call_user_func_array($this->services[$name], $args);
		}

		// Return the service callback return value.
		return $this->service_callback_ret_vals[$name];
	}

	/**
	 * Get a fresh instance of the service.
	 * 
	 * @param     string      $name       The name of the service.
	 * @param     mixed       $args       Args that get passed to the service callback.
	 * @return    mixed                   The value returned from the service callback.
	 */
	public function fresh_service(string $name, ...$args) {

		// Unset the callback return value for this service.
		unset($this->service_callback_ret_vals[$name]);

		// Add the service name to the args array and return the service callback return value.
		array_unshift($args, $name);
		return call_user_func_array([$this, 'get_service'], $args);
	}

	/**
	 * Get the names of all services.
	 * 
	 * @return  array
	 */
	public function get_services(): array {
		return array_keys($this->services);
	}

	/**
	 * Remove a service by name.
	 * 
	 * @param     string   $name   The name of the service.
	 * @return    void
	 */
	public function remove_service(string $name) {
		unset($this->services[$name]);
		unset($this->service_callback_ret_vals[$name]);
	}

	/**
	 * Clear all services.
	 * 
	 * @return  void
	 */
	public function clear_services() {
		$this->services = [];
		$this->service_callback_ret_vals = [];
	}

	/**
	 * Whether or not the app has the service.
	 * 
	 * @param    string  $name   The name of the service.
	 * @return   bool
	 */
	public function has_service(string $name): bool {
		return isset($this->services[$name]);
	}

	protected function register_abstract_route() {
		return $this->route_store['abstract'];
	}

	protected function register_route() {
		return $this->route_store['non_abstract'];
	}

	protected function register_no_route() {
		return $this->route_store['no_route'];
	}

	public function register_routes() {}

	public function run_hook(string $name, bool $persist = true, ...$args) {

		$res = [];

		$hook_run = false;
		if (method_exists($this, $this->app()->config('hook_prefix') . $name)) {
			$res[$this->name()] = call_user_func_array([$this, $this->app()->config('hook_prefix') . $name], $args);
			$hook_run = true;
		}

		if (!$hook_run || $persist) {
			if (method_exists($this->app(), $this->app()->config('hook_prefix') . $name))
				$res['app'] = call_user_func_array([$this->app(), $this->app()->config('hook_prefix') . $name], $args);
		}

		return $persist ? $res : current($res);
	}

	public function include(string $file) {
		$scope = $this->app()->scope;
		include $file;
	}
}