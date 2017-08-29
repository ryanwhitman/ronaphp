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

/**
 * A resource. This class was made to be extended.
 */
abstract class Resource {

	/**
	 * The app.
	 * 
	 * @var \Rona\App
	 */
	protected $app;

	/**
	 * The module.
	 * 
	 * @var \Rona\Module
	 */
	protected $module;

	/**
	 * The constructor.
	 * 
	 * @param   object     $app_or_module      An instance of either a Rona app or Rona module.
	 * @param   mixed      $args               Any number of args that will get passed to the secondary construct method.
	 */
	public function __construct($app_or_module, ...$args) {

		// Set the app/module.
		if (is_a($app_or_module, '\Rona\App')) {
			$this->app = $app_or_module;
			unset($this->module);
		} else if (is_a($app_or_module, '\Rona\Module')) {
			$this->module = $app_or_module;
			unset($this->app);
		} else
			throw new \Execption('The 1st argument passed into a resource must be an instance of either a Rona app or Rona module.');

		// If it exists, execute the secondary construct method.
		if (method_exists($this, 'construct'))
			call_user_func_array([$this, 'construct'], $args);
	}
}