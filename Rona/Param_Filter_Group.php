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

	public function get(string $name) {
		return $this->filters[$name] ?? false;
	}

	public function success($data): Response {
		return new Response(true, NULL, $data);
	}

	public function failure(string $reason, $data = NULL): Response {
		return new Response(false, $reason, $data);
	}
}