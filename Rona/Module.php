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
	 * An array that holds services.
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

		// Create a config object for this module.
		$this->config = new Config;

		// Register this module's config.
		$this->register_config();

		// Register this module's services.
		$this->register_services();

		// Create route store objects for this module.
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
	 * A holding method to register services.
	 * 
	 * @return void
	 */
	protected function register_services() {}

	/**
	 * Get a service.
	 * 
	 * @param     string      $name       The name of the service.
	 * @param     bool        $get_new    If true, the service callback will be executed and returned. This allows the developer to get a new instantiated object, for instance.
	 * @return    mixed                   The value returned from the service callback.
	 */
	public function get_service(string $name, bool $get_new = false) {

		// Ensure the service has been registered.
		if (!isset($this->services[$name]))
			throw new Exception("The service '$name' has not been registered.");

		// If either the service callback has not already been executed or "$get_new" is true, execute the service callback.
		if (!isset($this->service_callback_ret_vals[$name]) || $get_new)
			$this->service_callback_ret_vals[$name] = $this->services[$name]($this);

		// Return the service callback return value.
		return $this->service_callback_ret_vals[$name];
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