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
	 * The module.
	 * 
	 * @var \Rona\Module
	 */
	protected $module;

	/**
	 * The constructor.
	 * 
	 * @param   object     $module      An instance of a module.
	 * @param   mixed      $args        Any number of args that will get passed to the secondary construct method.
	 */
	public function __construct(Module $module, ...$args) {

		// Set the module.
		$this->module = $module;

		// If it exists, execute the secondary construct method.
		if (method_exists($this, 'construct'))
			call_user_func_array([$this, 'construct'], $args);
	}

	/**
	 * Use the magic call method to allow some of the module's methods to be called from within the resource.
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
				'get_resources',
				'remove_resource',
				'clear_resources',
				'replace_resource',
				'has_resource'
			]) &&
			method_exists($this->module, $name)
		) {
			return call_user_func_array([$this->module, $name], $arguments);
		}

		// Since the method wasn't able to be called, throw an exception.
		throw new \Exception("The method '$name' does not exist in the " . __CLASS__ . ' class.');
	}
}