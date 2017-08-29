<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2017 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
 */

namespace Rona\Resource;

/**
 * A module resource. This class was made to be extended.
 */
abstract class Module {

	/**
	 * The module.
	 * 
	 * @var \Rona\Module
	 */
	protected $module;

	/**
	 * The constructor.
	 * 
	 * @param   \Rona\Module    $module     An instance of a Rona module.
	 * @param   mixed           $args       Any number of args that will get passed to the secondary construct method.
	 */
	public function __construct(\Rona\Module $module, ...$args) {

		// Set the module.
		$this->module = $module;

		// If it exists, execute the secondary construct method.
		if (method_exists($this, 'construct'))
			call_user_func_array([$this, 'construct'], $args);
	}
}