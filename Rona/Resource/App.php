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
 * An app resource. This class was made to be extended.
 */
abstract class App {

	/**
	 * The app.
	 * 
	 * @var \Rona\App
	 */
	protected $app;

	/**
	 * The constructor.
	 * 
	 * @param   \Rona\App   $app     An instance of a Rona app.
	 * @param   mixed       $args    Any number of args that will get passed to the secondary construct method.
	 */
	public function __construct(\Rona\App $app, ...$args) {

		// Set the app.
		$this->app = $app;

		// If it exists, execute the secondary construct method.
		if (method_exists($this, 'construct'))
			call_user_func_array([$this, 'construct'], $args);
	}
}