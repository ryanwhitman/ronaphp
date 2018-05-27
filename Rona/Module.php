<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.4.0
 */

namespace Rona;

use Rona\Routing\Store;
use Rona\Config\Config;

class Module {

	/**
	 * The module ID.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * The Rona instance this module belongs to.
	 *
	 * @var \Rona
	 */
	protected $app;

	/**
	 * A config instance.
	 *
	 * @var \Rona\Config\Config
	 */
	protected $config;

	/**
	 * An array that holds the resources.
	 *
	 * @var array
	 */
	protected $resources = [];

	/**
	 * An array that holds the param filter groups.
	 *
	 * @var array
	 */
	protected $param_filter_groups = [];

	/**
	 * An array that holds the instantiated param filter groups.
	 *
	 * @var array
	 */
	protected $param_filter_group_objects = [];

	/**
	 * An array that holds the procedure groups.
	 *
	 * @var array
	 */
	protected $procedure_groups = [];

	/**
	 * An array that holds the instantiated procedure groups.
	 *
	 * @var array
	 */
	protected $procedure_group_objects = [];

	/**
	 * An array to hold hooks.
	 *
	 * @var array
	 */
	protected $hooks = [];

	/**
	 * The class constructor.
	 *
	 * @param    string    $id     The module ID.
	 * @param    \Rona     $app    A Rona instance.
	 */
	public function __construct(string $id, Rona $app) {

		// Set this module's ID.
		$this->id = $id;

		// Set the Rona instance.
		$this->app = $app;

		// Create a config object for the module.
		$this->config = new Config;

		// Register this module's config.
		$this->register_config();

		// Create route store objects for this module.
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

	public function config(string $item = NULL, bool $traverse_up = false) {
		if (is_null($item))
			return $this->config;
		if (!$traverse_up || $this->config->isset($item))
			return $this->config->get($item);
		return $this->app_config($item);
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
	public function register_resources() {}

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
			throw new \Exception("The resource '$name' has already been registered in the module {$this->get_id()}.");

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
			throw new \Exception("The resource '$name' in the module {$this->get_id()} needs either a class name (string) or callback (callable).");
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
			throw new \Exception("The resource '$name' has not been registered.");

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

	/**
	 * A holding method to register param filter groups.
	 *
	 * @return void
	 */
	public function register_param_filter_groups() {}

	/**
	 * Register a param filter group.
	 *
	 * @param   string    $group_name  The group name.
	 * @param   string    $class_name  The name of the class that contains the group. This will be instantiated later on.
	 * @return  void
	 */
	public function register_param_filter_group(string $group_name, string $class_name) {

		// Ensure the param filter group name is unique.
		if (isset($this->param_filter_groups[$group_name]))
			throw new \Exception("Param filter group names must be unique, but '$group_name' is already in use in the {$this->get_id()} module.");

		// Ensure the qualified class name is a subclass of the \Rona\Param_Filter_Group class.
		if (!is_subclass_of($class_name, '\Rona\Param_Filter_Group'))
			throw new \Exception("Param filter groups must be a subclass of '\Rona\Param_Filter_Group', but '$class_name' is not.");

		// Store this param filter group.
		$this->param_filter_groups[$group_name] = $class_name;
	}

	/**
	 * Get a param filter.
	 *
	 * @param   string   $full_name   The full name of the filter, including group name. The group name and filter name should be separated with a period.
	 * @return  array|false           An array if the filter is found, false otherwise.
	 */
	public function get_param_filter(string $full_name) {

		// Convert the full name into an array and grab the parts.
		$filter_name_parts = explode('.', $full_name);
		$group_name = $filter_name_parts[0];
		$filter_name = $filter_name_parts[1];

		if (isset($this->param_filter_groups[$group_name])) {

			if (!isset($this->param_filter_group_objects[$group_name]))
				$this->param_filter_group_objects[$group_name] = new $this->param_filter_groups[$group_name]($group_name, $this);

			return $this->param_filter_group_objects[$group_name]->get($filter_name);
		}

		return false;
	}

	/**
	 * Locate a param filter by passing in either the filter name as a string or an array with the filter module and filter name.
	 *
	 * @param   string|array   $filter_to_locate  The filter to locate.
	 * @return  array|false                       The located filter. False will be returned when the filter is not found.
	 */
	public function locate_param_filter($filter_to_locate) {

		// Create an empty array to hold the filter that is located.
		$located_filter = false;

		// When the filter to locate is a string:
		if (is_string($filter_to_locate)) {
			$filter = $this->get_param_filter($filter_to_locate);
			if ($filter)
				$located_filter = $filter;
		}

		// When the filter to locate is an array:
		else if (
			is_array($filter_to_locate) &&
			count($filter_to_locate) == 2 &&
			isset($filter_to_locate[0]) &&
			isset($filter_to_locate[1]) &&
			is_string($filter_to_locate[1])
		) {

			// Default the found filter to false.
			$filter = false;

			// The module should be stored at index 0.
			$filter_module = $filter_to_locate[0];

			// The filter name should be stored at index 1.
			$filter_name = $filter_to_locate[1];

			// When the filter module is just a string, convert it to a module instance.
			if (is_string($filter_module))
				$filter_module = $this->get_module($filter_module);

			// If the filter module is a module instance, grab the param filter.
			if ($filter_module instanceof Module)
				$filter = $filter_module->get_param_filter($filter_name);

			// If a filter was found, store it.
			if ($filter)
				$located_filter = $filter;
		}

		// Return the located filter.
		return $located_filter;
	}

	/**
	 * A holding method to register procedure groups.
	 *
	 * @return void
	 */
	public function register_procedure_groups() {}

	/**
	 * Register a procedure group.
	 *
	 * @param   string    $group_name  The group name.
	 * @param   string    $class_name  The name of the class that contains the group. This will be instantiated later on.
	 * @return  void
	 */
	public function register_procedure_group(string $group_name, string $class_name) {

		// Ensure the procedure group name is unique.
		if (isset($this->procedure_groups[$group_name]))
			throw new \Exception("Procedure group names must be unique, but '$group_name' is already in use in the {$this->get_id()} module.");

		// Ensure the qualified class name is a subclass of the \Rona\Procedure_Group class.
		if (!is_subclass_of($class_name, '\Rona\Procedure_Group'))
			throw new \Exception("Procedure Groups must be a subclass of '\Rona\Procedure_Group', but '$class_name' is not.");

		// Store this procedure group.
		$this->procedure_groups[$group_name] = $class_name;
	}

	/**
	 * Execute a procedure.
	 *
	 * @param    string             $full_procedure_name     The full name of the procedure, including group name. The group name and procedure name should be separated with a period.
	 * @param    array              $input                   The input data to pass to the procedure.
	 * @param    string             $mode                    The exact procedure method to run.
	 * @return   Response
	 */
	public function run_procedure(string $full_procedure_name, array $input = [], string $mode = 'run') {
		$full_procedure_name = explode('.', $full_procedure_name);
		$group_name = $full_procedure_name[0];
		$procedure_name = $full_procedure_name[1];
		if (!isset($this->procedure_group_objects[$group_name]))
			$this->procedure_group_objects[$group_name] = new $this->procedure_groups[$group_name]($group_name, $this);

		switch ($mode) {
			case 'process_input': return $this->procedure_group_objects[$group_name]->process_input($procedure_name, $input);
			case 'execute': return $this->procedure_group_objects[$group_name]->execute($procedure_name, $input);
			case 'run': return $this->procedure_group_objects[$group_name]->run($procedure_name, $input);
		}

		throw new \Exception('The mode must be one of the following: process_input, execute, run');
	}

	/**
	 * A shortcut for calling a specific module's run_procedure method.
	 *
	 * @see \Rona\Module::run_procedure()
	 *
	 * @param   string   $module_id   The module ID.
	 */
	public function run_module_procedure(string $module_id, ...$args) {
		return call_user_func_array([$this->get_module($module_id), 'run_procedure'], $args);
	}

	/**
	 * A holding method to register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {}

	/**
	 * Register a hook.
	 *
	 * @param    string     $name        The hook name.
	 * @param    \Closure   $callback    The hook callback.
	 * @return   void
	 */
	public function register_hook(string $name, \Closure $callback) {

		// Ensure the hook hasn't already been registered.
		if (isset($this->hooks[$name]))
			throw new \Exception("The hook '$hook' in module '{$this->get_id()}' has already been registered.");

		// Store the hook.
		$this->hooks[$name] = $callback;
	}

	/**
	 * Run a hook.
	 *
	 * @param    string   $name   The hook name.
	 * @param    mixed    $args   Optional args to pass to the hook callback.
	 * @return   mixed            If the hook exists, the return value of the hook callback, NULL otherwise.
	 */
	public function run_hook(string $name, ...$args) {

		// If this module contains the hook, execute it and return the response.
		if (isset($this->hooks[$name]))
			return call_user_func_array($this->hooks[$name], $args);

		// The hook doesn't exist, so just return NULL.
		return;
	}

	/**
	 * A holding method for registering routes.
	 *
	 * @return void
	 */
	public function register_routes() {}

	/**
	 * Register an abstract route.
	 *
	 * @return  \Rona\Routing\Store    A route store instance.
	 */
	public function register_abstract_route(): Store {
		return $this->route_store['abstract'];
	}

	/**
	 * Register a non-abstract route.
	 *
	 * @return  \Rona\Routing\Store    A route store instance.
	 */
	public function register_route(): Store {
		return $this->route_store['non_abstract'];
	}

	/**
	 * Register a no-route.
	 *
	 * @return  \Rona\Routing\Store    A route store instance.
	 */
	public function register_no_route(): Store {
		return $this->route_store['no_route'];
	}

	/**
	 * Include a template file within the module class and set the scope object. This allows template/view files to have direct access to the module use "$this".
	 *
	 * @param    Rona\Scope   $scope   The scope object.
	 * @param    string       $file    The file path to include.
	 * @return   void
	 */
	public function include_template_file(Scope $scope, string $file) {
		include $file;
	}
}