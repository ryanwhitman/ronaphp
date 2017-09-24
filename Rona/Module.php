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
use Rona\Rona;
use Rona\Routing\Store;
use Rona\Config\Config;
use Rona\Response;

class Module {

	protected $id;

	protected $app;

	protected $config;

	/**
	 * An array that holds the resources.
	 * 
	 * @var array
	 */
	protected $resources = [];

	public function __construct(Rona $app, string $id = NULL) {

		// If a module ID was passed in thru the construct, set it.
		if (!is_null($id))
			$this->id = $id;

		// Prepare the module ID and ensure it exists.
		$this->id = strtolower(trim($this->get_id()));
		if (!$this->get_id())
			throw new Exception('A module must have an ID.');

		// Set the Rona instance.
		$this->app = $app;

		// Create a config object for the module.
		$this->config = new Config;

		// Register the module's config.
		$this->register_config();

		// Register the module's resources.
		$this->register_resources();

		// Create route store objects for the module.
		$this->route_store = [
			'abstract'			=> new Store($this->app_config('http_methods')),
			'non_abstract'		=> new Store($this->app_config('http_methods')),
			'no_route'			=> new Store($this->app_config('http_methods'))
		];
	}

	public function get_id() {
		return $this->id;
	}

	public function get_app(): Rona {
		return $this->app;
	}

	public function config(string $item = NULL) {
		return is_null($item) ? $this->config : $this->config->get($item);
	}

	/**
	 * A shortcut for calling the app's config method.
	 *
	 * @see \Rona\Rona::config()
	 */
	public function app_config(...$args) {
		return call_user_func_array([$this->get_app(), 'config'], $args);
	}

	/**
	 * A shortcut for calling a specific module's config method.
	 *
	 * @see \Rona\Module::config()
	 * 
	 * @param   string   $module_id   The module ID.
	 */
	public function module_config(string $module_id, ...$args) {
		return call_user_func_array([$this->get_module($module_id), 'config'], $args);
	}

	protected function register_config() {}

	/**
	 * A shortcut for calling the app's get_modules method.
	 *
	 * @see \Rona\Rona::get_modules()
	 */
	public function get_modules(...$args) {
		return call_user_func_array([$this->get_app(), 'get_modules'], $args);
	}

	/**
	 * A shortcut for calling the app's get_module method.
	 *
	 * @see \Rona\Rona::get_module()
	 */
	public function get_module(...$args) {
		return call_user_func_array([$this->get_app(), 'get_module'], $args);
	}

	public function module_registered() {}

	/**
	 * A holding method to register resources.
	 * 
	 * @return void
	 */
	protected function register_resources() {}

	/**
	 * Register a resource.
	 * 
	 * @param     string             $name                      The name of the resource.
	 * @param     string|callable    $class_name_or_callback    Either a string which represents a class name or a callable callback. If a string is provided, the class will be instantiated with the passed-in args (the module will be the first arg) at time of execution (when "get_resource" is run).
	 * @return    void
	 */
	public function register_resource(string $name, $class_name_or_callback) {

		// Ensure resource name hasn't already been registered.
		if (isset($this->resources[$name]))
			throw new Exception("The resource '$name' has already been registered in the module {$this->get_id()}.");

		// Set the resource.
		if (is_string($class_name_or_callback)) {
			$this->resources[$name] = function($module, ...$args) use ($class_name_or_callback) {
				$class = new \ReflectionClass($class_name_or_callback);
				array_unshift($args, $module);
				return $class->newInstanceArgs($args);
			};
		} else if (is_callable($class_name_or_callback))
			$this->resources[$name] = $class_name_or_callback;
		else
			throw new Exception("The resource '$name' in the module {$this->get_id()} needs either a class name (string) or callback (callable).");
	}

	/**
	 * Get a resource.
	 * 
	 * @param     string      $name       The name of the resource.
	 * @param     mixed       $args       Args that get passed to the resource callback.
	 * @return    mixed                   The value returned from the resource callback.
	 */
	public function get_resource(string $name, ...$args) {

		// Ensure the resource has been registered.
		if (!isset($this->resources[$name]))
			throw new Exception("The resource '$name' has not been registered.");

		// Add the module instance to the args and execute and return the resource.
		array_unshift($args, $this);
		return call_user_func_array($this->resources[$name], $args);
	}

	/**
	 * A shortcut for calling a specific module's get_resource method.
	 *
	 * @see \Rona\Module::get_resource()
	 * 
	 * @param   string   $module_id   The module ID.
	 */
	public function get_module_resource(string $module_id, ...$args) {
		return call_user_func_array([$this->get_module($module_id), 'get_resource'], $args);
	}

	/**
	 * Get the names of all resources.
	 * 
	 * @return  array
	 */
	public function get_resources(): array {
		return array_keys($this->resources);
	}

	/**
	 * Remove a resource by name.
	 * 
	 * @param     string   $name   The name of the resource.
	 * @return    void
	 */
	public function remove_resource(string $name) {
		unset($this->resources[$name]);
	}

	/**
	 * Clear all resources.
	 * 
	 * @return  void
	 */
	public function clear_resources() {
		$this->resources = [];
	}

	/**
	 * Register a resource, regardless of whether or not it has already been registered.
	 * 
	 * @param     string      $name        The name of the resource.
	 * @param     callable    $callback    The callback to execute.
	 * @return    void
	 */
	public function replace_resource(string $name, callable $callback) {
		$this->resources[$name] = $callback;
	}

	/**
	 * Whether or not the module has the resource.
	 * 
	 * @param    string  $name   The name of the resource.
	 * @return   bool
	 */
	public function has_resource(string $name): bool {
		return isset($this->resources[$name]);
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

	public function include(string $file) {
		$scope = $this->get_app()->scope;
		include $file;
	}
}