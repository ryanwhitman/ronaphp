<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.5.0
 */

namespace Rona;

abstract class Module_Extension {

	/**
	 * The module.
	 *
	 * @var \Rona\Module
	 */
	protected $module;

	/**
	 * Use the magic call method to allow some of the module's methods to be called from within the extension.
	 *
	 * @param   string    $name        The name of the method being called.
	 * @param   array     $arguments   An enumerated array containing the parameters passed to the $name'ed method.
	 * @return  mixed                  The return value of the executed method.
	 *
	 * @throws  Exception              An exception is thrown when the method is unable to be called.
	 */
	public function __call(string $name, array $arguments) {

		// If it is able to be called, execute the module method.
		if (
			in_array($name, [
				'get_id',
				'get_app',
				'config',
				'app_config',
				'module_config',
				'get_modules',
				'get_module',
				'get_resource',
				'get_module_resource',
				'run_module_procedure'
			]) &&
			method_exists($this->module, $name)
		) {
			return call_user_func_array([$this->module, $name], $arguments);
		}

		// Since the method wasn't able to be called, throw an exception.
		throw new \Exception("The method '$name' does not exist in the " . __CLASS__ . ' class.');
	}
}