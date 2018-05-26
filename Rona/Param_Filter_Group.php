<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.3.1
 */

namespace Rona;

class Param_Filter_Group extends Module_Extension {

	protected $name;

	protected $module;

	protected $filters = [];

	public function __construct(string $name, \Rona\Module $module) {

		// Set the name.
		$this->name = $name;

		// Set the module.
		$this->module = $module;

		// Register the filters.
		$this->register_filters();
	}

	protected function register_filters() {}

	public function register(string $name, array $default_options, \Closure $callback) {
		$this->filters[$name] = [
			'default_options'	=> $default_options,
			'callback'			=> $callback
		];
	}

	/**
	 * Copy an existing filter by modifying the default options.
	 *
	 * @param    string|array    $filter_to_copy   The filter to copy.
	 * @param    string          $new_name         The name of the new filter.
	 * @param    array           $new_options      New options that will get merged with the existing options.
	 * @return   void
	 */
	public function copy($filter_to_copy, string $new_name, array $new_options = []) {

		// Locate the filter that is to be copied.
		$filter_to_copy = $this->module->locate_param_filter($filter_to_copy);

		// Ensure a valid filter was found.
		if (empty($filter_to_copy)) {
			throw new \Exception("The param filter $new_name identified in the module '{$this->module->get_id()}' is attempting to copy a filter that does not exist.");
		}

		// Merge the option arrays and register the new filter.
		$this->register($new_name, array_merge($filter_to_copy['default_options'], $new_options), $filter_to_copy['callback']);
	}

	public function get(string $name) {
		return $this->filters[$name] ?? false;
	}

	public function valid($transformed_value): Param_Filter_Response {
		return new Param_Filter_Response(true, NULL, $transformed_value);
	}

	public function invalid(string $tag, $data = NULL): Param_Filter_Response {
		return new Param_Filter_Response(false, $tag, $data);
	}
}